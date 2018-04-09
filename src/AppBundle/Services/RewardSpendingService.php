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
use AppBundle\Entity\User_Reward;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class RewardSpendingService
{
    private $em;

    private $logger;

    public function __construct(NotificationDispatcher $notificationDispatcher, MailDispatcher $mailDispatcher, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function applyReward(ContractFan $cf)
    {
        $this->em->persist($cf);
        $user_rewards = $cf->getUserRewards()->toArray();
        $isReduction = false;
        $today = new \DateTime();
        $this->clearPurchases($cf);
        foreach ($user_rewards as $user_reward) {
            if ($user_reward->getActive() && $user_reward->getLimitDate() > $today && $user_reward->getRemainUse() > 0) {
                $reward = $user_reward->getReward();
                if ($reward instanceof ReductionReward) {
                    $isReduction = true;
                    foreach ($cf->getPurchases() as $purchase) {
                        if ($user_reward->getCounterParts()->count() == 0
                            || $user_reward->getCounterParts()->contains($purchase->getCounterPart())) {
                            $this->applyPurchaseReduction($cf, $user_reward, $purchase);
                        }
                    }
                }
            } else {
                $cf->removeUserReward($user_reward);
            }
        }
        if ($isReduction) {
            $cf->setAmount($this->computeAmount($cf));
        }
        $this->em->flush();
    }

    public function consumeReward(ContractFan $cf)
    {
        $user_rewards = $cf->getUserRewards()->toArray();
        foreach ($user_rewards as $user_reward) {
            $user_reward->setRemainUse($user_reward->getRemainUse() - 1);
            if ($user_reward->getRemainUse() == 0) {
                $user_reward->setActive(false);
            }
            $this->em->persist($user_reward);
        }
        $this->em->flush();
    }

    public function getApplicableReward(ContractFan $cf)
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
                if (!$user_reward->getCounterParts()->isEmpty()) {
                    foreach ($user_reward->getCounterParts()->toArray() as $counter_part) {
                        $counterPartCounter = 0;
                        foreach ($cf->getPurchases()->toArray() as $purchase) {
                            if ($purchase->getCounterPart() == $counter_part) {
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

    public function setBaseAmount(ContractFan $cf)
    {
        $cf->setAmount($cf->getAmountWithoutReduction());
    }

    private function applyPurchaseReduction(ContractFan $cf, User_Reward $user_reward, Purchase $purchase)
    {
        $reduction = $user_reward->getRewardTypeParameters()['reduction'];
        $counter_part_price = $purchase->getCounterpart()->getPrice();
        $reductionPrice = $counter_part_price / 100 * $reduction;
        if ($purchase->getReducedPrice() == null) {
            $purchase->setReducedPrice($counter_part_price - $reductionPrice);
        } else {
            $purchase->setReducedPrice($purchase->getReducedPrice() - $reductionPrice);
        }
        if ($purchase->getReducedPrice() < 0) {
            $purchase->setReducedPrice(0);
        }

    }

    private function computeAmount(ContractFan $cf)
    {
        $amount = 0;
        foreach ($cf->getPurchases() as $purchase) {
            $amount += $purchase->getReducedAmount();
        }
        return $amount;
    }

    private function clearPurchases(ContractFan $cf)
    {
        foreach ($cf->getPurchases() as $purchase) {
            $purchase->setReducedPrice(null);
        }
    }

    public function checkDeadlines()
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
}