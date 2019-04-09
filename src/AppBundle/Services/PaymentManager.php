<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use AppBundle\Entity\YB\YBContractArtist;
use Doctrine\ORM\EntityManagerInterface;
use XBundle\Entity\Project;
use XBundle\ENtity\XContractFan;

class PaymentManager
{
    private $em;
    private $stripe_api_secret;
    private $notifier;
    private $mailer;
    private $rewardSpendingService;

    public function __construct(EntityManagerInterface $em, MailDispatcher $mailDispatcher, NotificationDispatcher $notificationDispatcher, $stripe_api_secret, RewardSpendingService $rewardSpendingService)
    {
        $this->em = $em;
        $this->stripe_api_secret = $stripe_api_secret;
        $this->mailer = $mailDispatcher;
        $this->notifier = $notificationDispatcher;
        $this->rewardSpendingService = $rewardSpendingService;
    }

    private function initStripe()
    {
        \Stripe\Stripe::setApiKey($this->stripe_api_secret);
    }

    public function refundStripeAndUMPayments(array $payments)
    {
        $this->initStripe();
        foreach ($payments as $payment) {
            $this->refundStripePayment($payment);
            $this->refundUMPayment($payment);
            $this->rewardSpendingService->refundReward($payment->getContractFan());
        }
        $this->em->flush();
    }

    public function refundStripeAndUMPayment(Payment $payment)
    {
        $this->initStripe();
        $this->refundStripePayment($payment);
        $this->refundUMPayment($payment);

        $this->em->flush();
    }
    
    public function refundStripeAndUMContractArtist(ContractArtist $contractArtist) {
        $this->initStripe();
        foreach($contractArtist->getContractsFanPaid() as $cf) {
            $this->refundPartOfStripePayment($cf);
            $this->refundUMContractFan($cf);
        }
        $this->em->flush();
    }

    public function refundStripeAndUMContractFan(ContractFan $contractFan) {
        $this->initStripe();
        $this->refundPartOfStripePayment($contractFan);
        $this->refundUMContractFan($contractFan);

        $this->em->flush();
    }

    public function refundStripePayment(Payment $payment)
    {
        if (!$payment->getRefunded()) {
            \Stripe\Refund::create(array(
                "charge" => $payment->getChargeId(),
            ));
        }
    }

    public function refundPartOfStripePayment(ContractFan $contractFan) {
        if(!$contractFan->getRefunded()) {
            $payment = $contractFan->getPayment();
            if (!$payment->getRefunded()) {
                \Stripe\Refund::create(array(
                    "charge" => $payment->getChargeId(),
                    "amount" => $contractFan->getAmount() * 100,
                ));
            }
        }
    }

    public function refundUMContractFan(ContractFan $cf) {
        if(!$cf->getRefunded()) {
            $cf->setRefunded(true);
            $ca = $cf->getContractArtist();

            // Remove refunded tickets from sold tickets
            // Unless crodwfunding is failed
            if($ca instanceof ContractArtist && !$ca->getFailed()) {
                $ca->removeCounterPartsSold($cf->getThresholdIncrease());
                $this->em->persist($ca);
            }

            $this->rewardSpendingService->refundReward($cf);

            $this->em->persist($cf);

            if(array_sum(array_map(function(ContractFan $contractFan) {
                    return $contractFan->getRefunded() ? 0 : 1;
                }, $cf->getCart()->getContracts()->toArray())) == 0) {
                $cf->getPayment()->setRefunded(true);
            }

            $this->notifyUserRefundedContractFan($cf);
        }
    }

    public function refundUMPayment(Payment $payment)
    {
        if (!$payment->getRefunded()) {
            $payment->setRefunded(true);

            foreach($payment->getContractsFan() as $cf) {
                /** @var ContractFan $cf */
                $cf->setRefunded(true);
                $ca = $cf->getContractArtist();

                // Remove refunded tickets from sold tickets
                // Unless crodwfunding is failed
                if($ca instanceof ContractArtist && !$ca->getFailed()) {
                    $ca->removeCounterPartsSold($cf->getThresholdIncrease());
                    $this->em->persist($ca);
                }

                $this->rewardSpendingService->refundReward($cf);

                $this->em->persist($cf);
            }

            $this->em->persist($payment);

            $this->notifyUserRefundedPayment($payment);
        }
        $this->em->flush();
    }

    public function notifyUserRefundedContractFan(ContractFan $cf) {
        $this->mailer->sendRefundedContractFan($cf);
    }

    public function notifyUserRefundedPayment(Payment $payment)
    {
        $this->mailer->sendRefundedPayment($payment);
    }

    // ---------- YB
    public function refundStripeAndYBContractArtist(YBContractArtist $campaign) {
        $this->initStripe();

        foreach($campaign->getContractsFanPaid() as $contractFan) {
            /** @var ContractFan $contractFan */
            $this->refundPartOfStripePayment($contractFan);
            $this->refundYBContractFan($contractFan);
        }

        $campaign->setRefunded(true);
        $this->em->persist($campaign);

        $this->em->flush();
    }

    public function refundYBContractFan(ContractFan $cf) {
        if(!$cf->getRefunded()) {
            $cf->setRefunded(true);
            $ca = $cf->getContractArtist();

            // Remove refunded tickets from sold tickets
            // Unless crodwfunding is failed
            if(!$ca->getFailed()) {
                $ca->removeCounterPartsSold($cf->getThresholdIncrease());
                $this->em->persist($ca);
            }

            // $this->rewardSpendingService->refundReward($cf);

            $this->em->persist($cf);

            if(array_sum(array_map(function(ContractFan $contractFan) {
                    return $contractFan->getRefunded() ? 0 : 1;
                }, $cf->getCart()->getContracts()->toArray())) == 0) {
                $cf->getPayment()->setRefunded(true);
            }

            $this->notifyUserRefundedYBContractFan($cf);
        }
    }


    public function notifyUserRefundedYBContractFan(ContractFan $cf) {
        $this->mailer->sendRefundedYBContractFan($cf);
    }


    // ---------- X
    public function refundStripeAndProject(Project $project) {
        $this->initStripe();
        
        foreach($project->getContributionsPaid() as $contribution) {
            /** @var XContractFan $contribution */
            $this->refundXPartOfStripePayment($contribution);
            $this->refundXContractFan($contribution);
        }

        $project->setRefunded(true);
        $this->em->persist($project);
        $this->em->flush();
    }

    public function refundXPartOfStripePayment(XContractFan $cf) {
        if(!$cf->getRefunded()) {
            $payment = $cf->getCart()->getPayment();
            if (!$payment->getRefunded()) {
                \Stripe\Refund::create(array(
                    "charge" => $payment->getChargeId(),
                    "amount" => $cf->getAmount() * 100,
                ));
            }
        }
    }

    public function refundXContractFan(XContractFan $cf) {
        if(!$cf->getRefunded()) {
            $cf->setRefunded(true);
            $cf->getPayment()->setRefunded(true);
            $this->mailer->sendRefundedProject($cf);
        }
    }

}