<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContractArtist;
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
        $this->rewardSpendingService->refundReward($payment->getContractFan());
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

    public function refundUMPayment(Payment $payment)
    {
        if (!$payment->getRefunded()) {
            $payment->setRefunded(true);
            $payment->getContractFan()->setRefunded(true);

            // Remove refunded tickets from sold tickets
            // Unless crodwfunding is failed
            if ($payment->getContractArtist() instanceof ContractArtist && !$payment->getContractArtist()->getFailed()) {
                $payment->getContractArtist()->removeTicketsSold($payment->getContractFan()->getCounterPartsQuantity());
            }

            $this->em->persist($payment);
            $this->em->persist($payment->getContractFan());
            $this->em->persist($payment->getContractArtist());

            $this->notifyUserRefundedPayment($payment);
        }
        $this->em->flush();
    }

    public function notifyUserRefundedPayment(Payment $payment)
    {
        $this->mailer->sendRefundedPayment($payment);
    }
}