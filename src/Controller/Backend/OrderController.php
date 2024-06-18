<?php

namespace App\Controller\Backend;

use App\Entity\Order\Order;
use App\Repository\Order\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/order', name: 'admin.order')]
class OrderController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
    ){}

    #[Route('/', name: '.index', methods: ['GET'])]
    public function index(OrderRepository $orderRepos): Response
    {

        $orders = $orderRepos->findAll();

        return $this->render('Backend/Order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}/show', name: '.show', methods: ['GET'])]
    public function show(?Order $order): Response| RedirectResponse
    {

        if (!$order){
            $this->addFlash('error', 'Aucune order trouver');
        }

        return $this->render('Backend/Order/show.html.twig', [
            'order' => $order
        ]);

    }

    #[Route('/{id}/cancel', name: '.cancel', methods: ['POST'])]
    public function cancel(?Order $order, Request $request): RedirectResponse
    {

        if (!$order){
            $this->addFlash('error', 'Aucune commande trouver');
            return $this->redirectToRoute('admin.order.index');
        }

        if ($this->isCsrfTokenValid('delete'. $order->getId(), $request->request->get('token'))){

            $order->setStatus(Order::STATUS_CANCELED);

            $this->em->persist($order);
            $this->em->flush();

        } else {
            $this->addFlash('error', 'invalide token csrf');
        }

        return $this->redirectToRoute('admin.order.index');

    }
}
