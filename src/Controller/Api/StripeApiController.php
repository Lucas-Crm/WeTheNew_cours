<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeApiController extends AbstractController
{
    #[Route('/api/stripe/api', name: 'app_api_stripe_api')]
    public function index(): Response
    {
        return $this->render('api/stripe_api/index.html.twig', [
            'controller_name' => 'StripeApiController',
        ]);
    }
}
