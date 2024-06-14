<?php

namespace App\EventListener;

use App\Entity\Order\Order;
use App\Entity\Order\Payement;
use App\Event\StripeEvent;
use App\Repository\Order\OrderRepository;
use App\Repository\Order\PayementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'payment_intent.succeeded', method: 'onPaymentSucceed')]
#[AsEventListener(event: 'payment_intent.payment_failed', method: 'onPaymentFailed')]
class StripeEventListener
{

    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepository,
        private PayementRepository $payementRepository,
        private LoggerInterface $logger
    ) {
    }

    public function onPaymentSucceed(StripeEvent $event): void
    {
        //On recupere la ressource de l'evenement
        $ressource = $event->getRessource();


        //On verifie que la ressource est bien une charge stripe
        if (!$ressource) {
            throw new \InvalidArgumentException('The event ressource is missing');
        }
        //On recupere le payment associe en BDD
        $payment = $this->payementRepository->find($ressource->metadata->payment_id);
        //On recupere la commande associe en BDD
        $order = $this->orderRepository->find($ressource->metadata->order_id);

        //On verifie que le paiement et la commande existent
        if (!$payment || !$order) {
            throw new \InvalidArgumentException('The payment or order is missing');
        }

        //On met a jour le paiement et la commande
        $payment->setStatus(Payement::STATUS_PAID);
        $order->setStatus(Order::STATUS_SHIPPING);

        $this->em->persist($payment);
        $this->em->persist($order);

        $this->em->flush();
    }

    public function onPaymentFailed(StripeEvent $event): void
    {
        $ressource = $event->getRessource();

        if (!$ressource) {
            throw new \InvalidArgumentException('The event ressource is missing');
        }

        $payment = $this->payementRepository->find($ressource->metadata->payment_id);
        $order = $this->orderRepository->find($ressource->metadata->order_id);

        if (!$payment || !$order) {
            throw new \InvalidArgumentException('The payment or order is missing');
        }

        $order->setStatus(Order::STATUS_AWAITING_PAYMENT);
        $payment->setStatus(Payement::STATUS_FAILED);

        $this->em->persist($order);
        $this->em->persist($payment);

        $this->em->flush();

    }

}
