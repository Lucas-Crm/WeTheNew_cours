<?php

namespace App\Controller\Frontend;

use App\Entity\Address;
use App\Entity\Delivery\Shipping;
use App\Entity\Order\Payement;
use App\Entity\User;
use App\Factory\StripeFactory;
use App\Form\AddressType;
use App\Form\PaymentType;
use App\Form\ShippingCheckoutType;
use App\Manager\CartManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/checkout', name: 'app.checkout')]
class CheckoutController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private CartManager $cartManager,
    ) {}

    #[Route('/address', name: '.address', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {

        $cart = $this->cartManager->getCurrentCart();

        if($cart->getOrderItems()->isEmpty()){
            $this->addFlash('danger', 'Vous devez avoir au minimum un produit pour acceder a cette page');
            return $this->redirectToRoute('app.products.index');
        }

        /**
         * @var User $user;
         */
        $user = $this->getUser();

        if($user->getDefaultAddressId()){
            $address = clone $user->getDefaultAddressId();
        } elseif (!$user->getAddresses()->isEmpty()){
            $address = clone $user->getAddresses()->first();
        } else {
            $address = new Address();
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!$user->hasAddress($address)){
                $user->addAddress($address);

                $this->em->persist($address);
                $this->em->flush();
            }

            return $this->redirectToRoute('app.checkout.shipping');
        }


        return $this->render('Frontend/Checkout/address.html.twig', [
            'cart' => $cart,
            'form' => $form,
            'addresses' => $user->getAddresses(),
        ]);
    }

    #[Route('/shipping', name: '.shipping', methods: ['GET', 'POST'])]
    public function shipping(Request $request): Response{

        $cart = $this->cartManager->getCurrentCart();

        if($cart->getOrderItems()->isEmpty()){
            $this->addFlash('error', 'Vous n\'avez pas de commande en cours');
            $this->redirectToRoute('app.cart.show');
        }

        if(!$cart->getShippings()->isEmpty()){
            $shipping = $cart->getShippings()->last();
        } else {
            $shipping = (new Shipping)
                ->setStatus(Shipping::STATUS_NEW);
        }

        $form = $this->createForm(ShippingCheckoutType::class, $shipping);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $shipping
                ->setOrderRef($cart)
                ->setStatus(Shipping::STATUS_NEW);


            $this->em->persist($shipping);
            $this->em->flush();

            return $this->redirectToRoute('app.checkout.recap');
        }

        return $this->render('Frontend/Checkout/shipping.html.twig', [
            'form' => $form,
            'cart' => $cart
        ]);

    }

    #[Route('/recap', name: '.recap', methods: ['GET', 'POST'])]
    public function recap(Request $request, StripeFactory $stripeFactory): Response | RedirectResponse
    {
        $cart = $this->cartManager->getCurrentCart();

        if($cart->getOrderItems()->isEmpty()){
            $this->addFlash('error', 'Vous n\'avez pas de commande en cours');
            return $this->redirectToRoute('app.cart.show');
        }

        $payment = (new Payement)
            ->setStatus(Payement::STATUS_NEW)
            ->setUser($this->getUser())
            ->setOrderRef($cart);

        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $payment->setStatus(Payement::STATUS_NEW);

            $this->em->persist($payment);
            $this->em->flush();


            $session = $stripeFactory->createSession($cart,
            $this->generateUrl('app.checkout.success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->generateUrl('app.checkout.cancel', [], UrlGeneratorInterface::ABSOLUTE_URL)
            );

            return $this->redirect($session->url);

        }


        return $this->render('Frontend/Checkout/recap.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    #[Route('/success', name: '.success', methods: ['GET'])]
    public function success(): Response
    {

        $this->addFlash('success', 'La commande a bien été payée ');
        return $this->redirectToRoute('app.home');
    }

    #[Route('/cancel', name: '.cancel', methods: ['GET'])]
    public function cancel(): Response
    {

        $this->addFlash('error', 'erreur lors du paiement');
        return $this->redirectToRoute('app.home');
    }

}
