<?php

namespace App\Controller;

use App\Entity\Type;
use App\Repository\TypeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TypeController extends AbstractController
{
    #[Route('api/types', name: 'api_Type', methods: ['GET'])]
    public function getAllType(TypeRepository $typeRepository, SerializerInterface $serializer): JsonResponse
    {
        $typeList = $typeRepository->findAll();

        $jsonTypeList = $serializer->serialize($typeList, 'json', ['groups' => 'getCards']);
        return new JsonResponse($jsonTypeList, Response::HTTP_OK, [], true);
    }

    #[Route('api/types/{id}', name: 'api_DetailType', methods: ['GET'])]
    public function getDetailType(SerializerInterface $serializer, Type $type): JsonResponse
    {
        $jsonType = $serializer->serialize($type, 'json', ['groups' => 'getCards']);
        return new JsonResponse($jsonType, Response::HTTP_OK, [], true);
    }
}
