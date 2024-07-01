<?php

namespace App\Controller\Api;

use App\Controller\Api\Traits\ApiControllerTrait;
use App\Entity\Product\Gender;
use App\Form\GenderType;
use App\Repository\Product\GenderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/gender', name: 'api.gender')]
class GenderApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private GenderRepository $genderRepos,
        private EntityManagerInterface $em,
    ){}

    #[Route('/', name: '.index', methods: ['GET'])]
    public function index(): JsonResponse
    {

        return $this->json([
            $this->genderRepos->findAll()
        ], 200, [], [
            'groups' => [
                'gender:read',
                'read'
            ]
        ]);
    }

    #[Route('/{id}', name: '.show', methods: ['GET'])]
    public function show(?Gender $gender): JsonResponse
    {

        if(!$gender){
            return $this->json([
                'status' => 'error',
                'msg' => 'Gender not found'
            ], 404);
        }

        return $this->json([
            $gender
        ], 200, [], [
            'groups' => [
                'gender:read',
                'gender:show',
                'read'
            ]
        ] );
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $gender = new Gender();

        $form = $this->createForm(GenderType::class, $gender, [
            'csrf_protection' => false
        ]);
        $form->submit(json_decode($request->getContent(), true), false);

        if($form->isSubmitted() && $form->isValid()){
            $this->em->persist($gender);
            $this->em->flush();
            return $this->json([
                'status' => 'success',
                'msg' => 'Le gender a bien ete cree',
                $gender
            ], 201, [], ['gender' => [
                'gender:read',
                'gender:show',
                'read'
            ]]);
        }

        return $this->getFormErrors($form);

    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    public function update(?Gender $gender, Request $request): JsonResponse
    {
        if(!$gender){
            return $this->json([
                'status' => 'errro',
                'message' => 'Gender Not Found'
            ], 404);
        }

        $form = $this->createForm(GenderType::class, $gender, [
            'csrf_protection' => false
        ]);
        $form->submit(json_decode($request->getContent(), true), false);

        if($form->isSubmitted() && $form->isValid()){
            $this->em->persist($gender);
            $this->em->flush();
            return $this->json([
                'status' => 'success',
                'msg' => 'Le gender a bien ete cree',
                $gender
            ], 201, [], ['gender' => [
                'gender:read',
                'gender:show',
                'read'
            ]]);
        }

        return $this->getFormErrors($form);

    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    public function delete(?Gender $gender): JsonResponse
    {
        if(!$gender){
            return $this->json([
                'status' => 'errro',
                'message' => 'Gender Not Found'
            ], 404);
        }


        $this->em->remove($gender);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Gender Deleted',
        ], 204);
    }


}
