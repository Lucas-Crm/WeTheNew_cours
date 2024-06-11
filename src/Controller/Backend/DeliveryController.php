<?php

namespace App\Controller\Backend;

use App\Entity\Delivery;
use App\Form\DeliveryType;
use App\Repository\DeliveryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/delivery', name: 'admin.delivery')]
class DeliveryController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em
    ){}

    #[Route('/', name: '.index')]
    public function index(DeliveryRepository $deliveryRepos): Response
    {

        $deliverys = $deliveryRepos->findAll();

        return $this->render('Backend/Delivery/index.html.twig', [
            'deliverys' => $deliverys,
        ]);
    }

    #[Route('/{id}/update', name: '.update', methods: ['GET', 'POST'])]
    public function update(?Delivery $delivery, Request $request): Response | RedirectResponse
    {

        if(!$delivery){
            $this->addFlash('error', 'Aucune delivery trouver');

            return $this->redirectToRoute('admin.delivery.index');
        }

        $form = $this->createForm(DeliveryType::class, $delivery);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->em->persist($delivery);
            $this->em->flush();

            $this->addFlash('success', 'La delivery a bien ete mise a jour');

            return $this->redirectToRoute('admin.delivery.index');
        }

        return $this->render('Backend/Delivery/update.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: '.delete', methods: ['POST'])]
    public function delete(?Delivery $delivery, Request $request): RedirectResponse
    {

        if(!$delivery){
            $this->addFlash('error', 'Aucune delivery trouver');

            return $this->redirectToRoute('admin.delivery.index');
        }

        if($this->isCsrfTokenValid('delete' . $delivery->getId(), $request->request->get('token'))){
            $this->em->remove($delivery);
            $this->em->flush();


            $this->addFlash('success', 'La delivery a bien ete supprimer');
        } elseif (!$this->isCsrfTokenValid('delete' . $delivery->getId(), $request->request->get('token'))){
            $this->addFlash('error', 'Mauvais token CSRF');
        }
            return $this->redirectToRoute('admin.delivery.index');
    }

    #[Route('/create', name: '.create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response | RedirectResponse
    {

        $delivery = new Delivery();

        $form = $this->createForm(DeliveryType::class, $delivery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->persist($delivery);
            $this->em->flush();

            $this->addFlash('success', 'la delivery a bien ete cree');

            return $this->redirectToRoute('admin.delivery.index');
        }


        return $this->render('Backend/Delivery/create.html.twig', [
            'form' => $form
        ]);


    }

}
