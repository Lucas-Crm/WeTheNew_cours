<?php

namespace App\Factory;

use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Event\StripeEvent;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class StripeFactory
{

    public function __construct(
        private string $stripeSecretKey,
        private string $stripeWebhookSecret,
        private EventDispatcherInterface $eventDispatcher,
    ) {
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

            'line_items' => array_map(function (OrderItem $orderItem): array {
                return [
                    'quantity' => $orderItem->getQuantity(),
                    'price_data' => [
                        'currency' => 'EUR',
                        'product_data' => [
                            'name' => $orderItem->getQuantity() . ' x ' . $orderItem->getProductVariant()->getProduct()->getName(),
                        ],
                        'unit_amount' => bcmul($orderItem->getPriceTTC() / $orderItem->getQuantity(), 100),
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

    /**
     *
     * Permet d'analyser la requete stripe et de retourner l'evenement correspondant
     *
     * @param string $signature La signature stripe de la requete
     * @param mixed $body $body le contenu de la requete
     * @return JsonResponse
     */
    public function handleStripeRequest(string $signature, mixed $body): JsonResponse
    {

        if (!$body) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Missing Body Content',
            ], 404);
        }

        $event = $this->getEvent($signature, $body);

        //Si event est de la classe JsonResponse on return event car il y a une erreur
        if ($event instanceof JsonResponse) {
            return $event;
        }

        $event = new StripeEvent($event);

        $this->eventDispatcher->dispatch($event, $event->getName());

        //TODO: Gestion des evenement stripe et persistance en BDD

        return new JsonResponse([
            "status" => 'success',
            'message' => 'Event received ans processed successfully'
        ]);
    }

    /**
     *
     * Permet de decoder la requete stripe et de retourner l'evenement correspondant
     *
     *
     * @param string $signature la signature stripe de la requete
     * @param mixed $body le contenu de la requete
     * @return Event|JsonResponse l'evenement stripe ou une reponse JSON d'erreur
     */
    private function getEvent(string $signature, mixed $body): Event | JsonResponse
    {
        try {

            $event = Webhook::constructEvent($body, $signature, $this->stripeWebhookSecret);
        } catch (\UnexpectedValueException $e) {

            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (SignatureVerificationException $e) {

            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return $event;
    }
}
