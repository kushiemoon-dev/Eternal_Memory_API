<?php

namespace App\Controller;

use App\Entity\Card;
use App\Repository\CardRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CardController extends AbstractController
{
    #[Route('api/cards', name: 'api_Card', methods: ['GET'])]
    public function getAllCard(CardRepository $cardRepository, SerializerInterface $serializer): JsonResponse
    {
        $cardList = $cardRepository->findAll();

        $jsonCardList = $serializer->serialize($cardList, 'json', ['groups' => 'getCards']);
        return new JsonResponse($jsonCardList, Response::HTTP_OK, [], true);
    }

    #[Route('api/cards/{id}', name: 'api_DetailCard', methods: ['GET'])]
    public function getDetailCard(SerializerInterface $serializer, Card $card): JsonResponse
    {
        $jsonCard = $serializer->serialize($card, 'json', ['groups' => 'getCards']);
        return new JsonResponse($jsonCard, Response::HTTP_OK, [], true);
    }
}
