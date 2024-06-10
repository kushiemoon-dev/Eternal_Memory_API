<?php

namespace App\Controller;

use App\Repository\CardRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class CardController extends AbstractController
{
    #[Route('api/cards', name: 'api_card', methods: ['GET'])]
    public function getCardList(CardRepository $cardRepository, SerializerInterface $serializer): JsonResponse
    {
        $cardList = $cardRepository->findAll();
        $jsonCardList = $serializer->serialize($cardList, 'json');
        return new JsonResponse($jsonCardList, Response::HTTP_OK, [], true);
    }
}
