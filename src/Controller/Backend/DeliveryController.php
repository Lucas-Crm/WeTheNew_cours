<?php

namespace App\Controller\Backend;

use App\Entity\Delivery;
use App\Repository\DeliveryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/delivery', name: 'admin.delivery')]
class DeliveryController extends AbstractController
{
    #[Route('/', name: '.index')]
    public function index(DeliveryRepository $deliveryRepos): Response
    {

        $deliverys = $deliveryRepos->findAll();

        return $this->render('Backend/Delivery/index.html.twig', [
            'deliverys' => $deliverys,
        ]);
    }

    #[Route('/{id}/update', name: '.update', methods: ['GET', 'POST'])]
    public function update(?Delivery $delivery): Response | RedirectResponse
    {

        if(!$delivery){
            $this->addFlash('success', 'La delivery as bien ete mise a jour');

            return $this->redirectToRoute('admin.delivery.index');
        }

    }

}
