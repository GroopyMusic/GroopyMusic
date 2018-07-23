<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;

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
                $ca->removeTicketsSold($cf->getTresholdIncrease());
                $this->em->persist($ca);
            }

            $this->rewardSpendingService->refundReward($cf);

            $this->em->persist($cf);

            $this->notifyUserRefundedContractFan($cf);
        }
    }

    public function refundUMPayment(Payment $payment)
    {
        if (!$payment->getRefunded()) {
            $payment->setRefunded(true);

            foreach($payment->getContractsFan() as $cf) {
                $cf->setRefunded(true);
                $ca = $cf->getContractArtist();

                // Remove refunded tickets from sold tickets
                // Unless crodwfunding is failed
                if($ca instanceof ContractArtist && !$ca->getFailed()) {
                    $ca->removeTicketsSold($cf->getTresholdIncrease());
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
}