<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 05/04/2018
 * Time: 13:45
 */

namespace AppBundle\Services;


use AppBundle\Entity\ConsomableReward;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\ReductionReward;
use AppBundle\Entity\RewardTicketConsumption;
use AppBundle\Entity\User_Reward;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class RewardSpendingService
{
    private $em;

    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * check if rewards are valid and apply it to contract fan, ticket, ...
     * if reward is a reduction, apply reduction to te correct purchase
     *
     * @param ContractFan $cf
     */
    public function applyReward(ContractFan $cf)
    {
        $this->em->persist($cf);
        $isReduction = false;
        $user_rewards = $cf->getUserRewards()->toArray();
        $today = new \DateTime();
        $this->clearPurchases($cf);
        foreach ($user_rewards as $user_reward) {
            if ($user_reward->getActive() && $user_reward->getLimitDate() > $today && $user_reward->getRemainUse() > 0) {
                $reward = $user_reward->getReward();
                $purchases = $this->getOrderedApplicablePurhcases($cf, $user_reward);
                if ($reward instanceof ReductionReward && $isReduction == false) {
                    $isReduction = true;
                    $remain_use = $user_reward->getRemainUse();
                    foreach ($purchases as $purchase) {
                        $remain_use = $this->computeReducedPrice($cf, $user_reward, $purchase, $remain_use);

                    }
                } elseif ($reward instanceof ReductionReward && $isReduction) {
                    $cf->removeUserReward($user_reward);
                    continue;
                }
                $this->setTicketReward($user_reward, $purchases, $cf);
            } else {
                $cf->removeUserReward($user_reward);
                continue;
            }
        }
        $this->em->flush();
    }

    /**
     * Consumes one use of all contract rewards
     *
     * @param ContractFan $cf
     *
     */
    public
    function consumeReward(ContractFan $cf)
    {
        $user_rewards = $cf->getUserRewards()->toArray();
        foreach ($user_rewards as $user_reward) {
            $reward = $user_reward->getReward();
            if ($reward instanceof ReductionReward) {
                $user_reward->setRemainUse($user_reward->getRemainUse() - $cf->getNbReducedCounterPart());
            } else {
                $user_reward->setRemainUse($user_reward->getRemainUse() - $cf->getCounterPartsQuantity());
            }
            if ($user_reward->getRemainUse() <= 0) {
                $user_reward->setRemainUse(0);
                $user_reward->setActive(false);
            }
            $this->em->persist($user_reward);
        }
        $this->em->flush();
    }


    /**
     * Checks if all contract rewards are valid
     * If a reward is not, it is removed from the contract
     *
     * @param ContractFan $cf
     * @return array
     */
    public
    function getApplicableReward(ContractFan $cf)
    {
        $applicableReward = [];
        $isApplicable = null;
        $hasCounterParts = null;
        $counterPartCounter = null;
        $user_rewards = $cf->getUserRewards();
        foreach ($user_rewards as $user_reward) {
            if ($user_reward->getBaseContractArtists()->isEmpty() && $user_reward->getBaseSteps()->isEmpty()
                && $user_reward->getArtists()->isEmpty() && $user_reward->getCounterParts()->isEmpty()) {
                $isApplicable = true;
                $hasCounterParts = true;
            } else {
                $isApplicable = true;
                $hasCounterParts = true;
                if (!$user_reward->getBaseContractArtists()->isEmpty() && !$user_reward->getBaseContractArtists()->contains($cf->getContractArtist())) {
                    $isApplicable = false;
                }
                if (!$user_reward->getBaseSteps()->isEmpty() && !$user_reward->getBaseSteps()->contains($cf->getContractArtist()->getStep())) {
                    $isApplicable = false;
                }
                if (!$user_reward->getArtists()->isEmpty() && !$user_reward->getArtists()->contains($cf->getContractArtist()->getArtist())) {
                    $isApplicable = false;
                }
                //if minimum 1 counter part match
                if (!$user_reward->getCounterParts()->isEmpty()) {
                    foreach ($user_reward->getCounterParts()->toArray() as $counter_part) {
                        $counterPartCounter = 0;
                        foreach ($cf->getPurchases()->toArray() as $purchase) {
                            if ($purchase->getCounterPart()->getId() == $counter_part->getId()) {
                                $counterPartCounter++;
                            }
                        }
                        if ($counterPartCounter == 0) {
                            $hasCounterParts = false;
                        }
                    }
                }
            }
            if ($isApplicable && $hasCounterParts) {
                array_push($applicableReward, $user_reward);
            }
        }
        return $applicableReward;
    }

    /**
     * Calculating the total amount of a contract
     *
     * @param ContractFan $cf
     *
     */
    public
    function setBaseAmount(ContractFan $cf)
    {
        $cf->setAmount($cf->getAmountWithoutReduction());
    }

    /**
     * Applies reward reductions to contracts
     *
     * @param ContractFan $cf
     * @param User_Reward $user_reward
     * @param Purchase $purchase
     * @param integer $remain_use
     * @return integer $remain_use
     */
    protected
    function computeReducedPrice(ContractFan $cf, User_Reward $user_reward, Purchase $purchase, $remain_use)
    {
        $reduction = $user_reward->getRewardTypeParameters()['reduction'];
        $counter_part_price = $purchase->getCounterpart()->getPrice();
        $quantityOrganic = $purchase->getQuantityOrganic();
        if ($remain_use > 0 && $quantityOrganic > 0) {
            if ($reduction >= 100) {
                $reductionPrice = $counter_part_price;
            } else {
                $reductionPrice = round($counter_part_price / 100 * $reduction, 2);
            }
            if ($purchase->getReducedPrice() == null) {
                $purchase->setReducedPrice(round($counter_part_price - $reductionPrice, 2)); // reduced price = price of each ticket non promotional
            }
            while ($remain_use > 0 && $quantityOrganic > 0) {
                $cf->setAmount($cf->getAmount() - $reductionPrice);
                $purchase->setNbReducedCounterparts($purchase->getNbReducedCounterparts() + 1);
                $remain_use = $remain_use - 1;
                $quantityOrganic = $quantityOrganic - 1;
            }
        }
        round($cf->getAmount(), 2);
        return $remain_use;
    }

    /**
     * Reset all purchases prices to null
     *
     * @param ContractFan $cf
     */
    protected
    function clearPurchases(ContractFan $cf)
    {
        foreach ($cf->getPurchases() as $purchase) {
            $purchase->setReducedPrice(null);
            $purchase->setNbReducedCounterparts(0);
        }
    }

    /**
     * checks all reward deadlines
     * If a reward deadline has passed, the active reward field changes to false
     *
     */
    public
    function checkDeadlines()
    {
        $user_rewards = $this->em->getRepository('AppBundle:User_Reward')->findAll();
        $now = new \DateTime();
        foreach ($user_rewards as $user_reward) {
            if ($user_reward->getLimitDate() < $now) {
                $user_reward->setActive(false);
                $this->em->persist($user_reward);
            }
        }
        $this->em->flush();
    }

    /**
     * for a reward, recovers all applicable purchases and ordering them by decreasing price
     * @param ContractFan $cf
     * @param User_Reward $user_reward
     * @return array
     */
    public
    function getOrderedApplicablePurhcases(ContractFan $cf, User_Reward $user_reward)
    {
        $purchases = $cf->getPurchases();
        $clearedPurchases = [];
        foreach ($purchases as $purchase) {
            if ($user_reward->getCounterParts()->count() == 0
                || $user_reward->getCounterParts()->contains($purchase->getCounterPart())) {
                array_push($clearedPurchases, $purchase);
            }
        }
        usort($clearedPurchases, function (Purchase $a, Purchase $b) {
            return $b->getCounterpart()->getPrice() - $a->getCounterpart()->getPrice();
        });
        return $clearedPurchases;
    }

    /**
     * assigns a reward to the right number of futur tickets
     *
     * @param User_Reward $user_reward
     * @param $purchases
     * @param ContractFan $contractFan
     */
    public function setTicketReward(User_Reward $user_reward, $purchases, ContractFan $contractFan)
    {
        $nbTimeGiveOut = 0;
        foreach ($purchases as $purchase) {
            $i = 1;
            if ($user_reward->getReward() instanceof ReductionReward) {
                while ($i <= $purchase->getNbReducedCounterparts()) {
                    $rewardTicketConsumption = new RewardTicketConsumption($user_reward, null, false, true);
                    $this->em->persist($rewardTicketConsumption);
                    $contractFan->addTicketReward($rewardTicketConsumption);
                    $purchase->addTicketReward($rewardTicketConsumption);
                    $i++;
                }
            } else {
                while ($i <= $purchase->getQuantity() && $nbTimeGiveOut < $user_reward->getRemainUse()) {
                    $rewardTicketConsumption = new RewardTicketConsumption($user_reward, null, false, true);
                    $this->em->persist($rewardTicketConsumption);
                    $contractFan->addTicketReward($rewardTicketConsumption);
                    $purchase->addTicketReward($rewardTicketConsumption);
                    $i++;
                    $nbTimeGiveOut = $nbTimeGiveOut + 1;
                }
            }
        }
    }

    /**
     * Assigns rewards to the corret tickets generated
     * @param ContractFan $cf
     */
    public function giveRewardToTicket(ContractFan $cf)
    {
        $this->em->persist($cf);
        foreach ($cf->getUserRewards()->toArray() as $user_reward) {
            $tickets = $cf->getTickets()->toArray();
            foreach ($cf->getTicketRewards()->toArray() as $ticketReward)
                if ($user_reward->getId() == $ticketReward->getUserReward()->getId()) {
                    $result = $this->findCorrespondingTicket($tickets, $ticketReward);
                    $tickets = $result[0];
                    if ($result[1] != null) {
                        $this->em->persist($result[1]->addReward($ticketReward));
                    }
                }
        }
    }

    /**
     *
     * Get the first ticket matches the reward ticket
     * @param $tickets
     * @param $ticketReward
     * @return array return [ array of remaining ticket , ticked finded ]
     */
    protected function findCorrespondingTicket($tickets, $ticketReward)
    {
        $ticketToReturn = null;
        $arrayToReturn = [];
        $isGiven = false;
        foreach ($tickets as $ticket) {
            if ($isGiven == false && $ticket->getCounterPart()->getId() == $ticketReward->getPurchase()->getCounterPart()->getId()) {
                $ticketToReturn = $ticket;
                $isGiven = true;
            } else {
                array_push($arrayToReturn, $ticket);
            }
        }
        return [$arrayToReturn, $ticketToReturn];
    }

    /*
     * refunds rewards to users
     */
    public function refundReward(ContractFan $contractFan)
    {
        $this->em->persist($contractFan);
        foreach ($contractFan->getTicketRewards()->toArray() as $ticketReward) {
            if ($ticketReward->getRefundable() === true && $ticketReward->getRefunded() === false) {
                $ticketReward->setRefunded(true);
                $user_reward = $ticketReward->getUserReward();
                $user_reward->setRemainUse($user_reward->getRemainUse() + 1);
                if ($user_reward->getActive() === false) {
                    $user_reward->setActive(true);
                }
            }
        }
    }

    //-------------PUBLIC METHOD FOR TEST (CALL PROTECTED METHOD)-------------//

    public function callComputeReducedPrice(ContractFan $cf, User_Reward $user_reward, Purchase $purchase, $remain_use)
    {
        return $this->computeReducedPrice($cf, $user_reward, $purchase, $remain_use);
    }

    public function callClearPurchases(ContractFan $cf)
    {
        $this->clearPurchases($cf);
    }

    public function callFindCorrespondingTicket($tickets, $ticketReward)
    {
        return $this->findCorrespondingTicket($tickets, $ticketReward);
    }


}