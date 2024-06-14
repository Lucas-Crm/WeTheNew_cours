<?php

namespace App\Factory;

use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Webmozart\Assert\Assert;

class StripeFactory
{

    public function __construct(
        private string $stripeSecretKey,
    ){
        Stripe::setApiKey($stripeSecretKey);
        Stripe::setApiVersion('2024-04-10');
    }

    /**
     *
     * Cree une session checkout stripe avec les infos de la commande
     * pour ensuite rediriger vers la page de paiement
     *
     * @return Session
     */
    public function createSession(Order $order, string $successUrl, string $cancelUrl): Session
    {
        Assert::notEmpty($order->getPayements(), 'You must have at least one payment to create Stripe session');

        return Session::create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $order->getUser()->getEmail(),

//          Line item pour stripe pour chaque produits on boucle avec array_map

            'line_items' => array_map(function(OrderItem $orderItem): array {
                return [
                    'quantity' => $orderItem->getQuantity(),
                    'price_data' => [
                        'currency' => 'EUR',
                        'product_data' => [
                            'name' => $orderItem->getQuantity() . ' x ' . $orderItem->getProductVariant()->getProduct()->getName(),
                        ],
                        'unit_amount' => bcmul($orderItem->getPriceTTC() / $orderItem->getQuantity(), 100) ,
                    ]
                ];

            }, $order->getOrderItems()->toArray()),

//            Shipping pour stripe

            'shipping_options' => [
                [
                    'shipping_rate_data' => [
                        'type' => 'fixed_amount',
                        'fixed_amount' => [
                            'currency' => 'EUR',
                            'amount' => $order->getShippings()->last()->getDelivery()->getPrice() * 100,
                        ],
                        'display_name' => $order->getShippings()->last()->getDelivery()->getName(),
                    ],
                ],
            ],


            'metadata' => [
                'order_id' => $order->getId(),
                'payment_id' => $order->getPayements()->last()->getId(),
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => $order->getId(),
                    'payment_id' => $order->getPayements()->last()->getId(),
                ]
            ],
        ]);

    }

}