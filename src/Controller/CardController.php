<?php

namespace App\Controller;

use App\Entity\Card;
use App\Repository\TypeRepository;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CardController extends AbstractController
{
    /**
 * This function retrieves all the cards from the database and returns them as a JSON response.
 *
 * @param CardRepository $cardRepository The repository for accessing card data.
 * @param SerializerInterface $serializer The serializer for converting objects to JSON.
 *
 * @return JsonResponse A JSON response containing the list of cards.
 *
 * @Route("/api/cards", name="cards", methods={"GET"})
 */
#[Route('/api/cards', name: 'cards', methods: ['GET'])]
public function getAllBooks(CardRepository $cardRepository, SerializerInterface $serializer): JsonResponse
{
    // Fetch all cards from the database
    $cardList = $cardRepository->findAll();
    
    // Serialize the card list to JSON using the 'getCards' group
    $jsonCardList = $serializer->serialize($cardList, 'json', ['groups' => 'getCards']);
    
    // Return the JSON response with HTTP status 200 (OK)
    return new JsonResponse($jsonCardList, Response::HTTP_OK, [], true);
}
	
    /**
 * This function retrieves a single card from the database based on its ID and returns it as a JSON response.
 *
 * @param Card $card The card entity to retrieve.
 * @param SerializerInterface $serializer The serializer for converting objects to JSON.
 *
 * @return JsonResponse A JSON response containing the requested card.
 *
 * @Route("/api/cards/{id}", name="detailCard", methods={"GET"})
 */
#[Route('/api/cards/{id}', name: 'detailCard', methods: ['GET'])]
public function getDetailBook(Card $card, SerializerInterface $serializer): JsonResponse {
    // Serialize the card object to JSON using the 'getCards' group
    $jsonCard = $serializer->serialize($card, 'json', ['groups' => 'getCards']);
    
    // Return the JSON response with HTTP status 200 (OK)
    return new JsonResponse($jsonCard, Response::HTTP_OK, [], true);
}
    
    
    /**
 * This function deletes a card from the database based on its ID.
 *
 * @param Card $card The card entity to delete.
 * @param EntityManagerInterface $em The entity manager for database operations.
 *
 * @return JsonResponse A JSON response with HTTP status 204 (No Content) if the deletion is successful.
 *
 * @Route("/api/cards/{id}", name="deleteCard", methods={"DELETE"})
 */
#[Route('/api/cards/{id}', name: 'deleteCard', methods: ['DELETE'])]
public function deleteCard(Card $card, EntityManagerInterface $em): JsonResponse {
    // Remove the card from the entity manager
    $em->remove($card);
    
    // Flush the changes to the database
    $em->flush();
    
    // Return a JSON response with HTTP status 204 (No Content)
    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}

    /**
 * This function creates a new card in the database based on the provided request data.
 *
 * @param Request $request The request object containing the card data.
 * @param SerializerInterface $serializer The serializer for converting objects to JSON.
 * @param EntityManagerInterface $em The entity manager for database operations.
 * @param UrlGeneratorInterface $urlGenerator The URL generator for creating absolute URLs.
 * @param TypeRepository $typeRepository The repository for accessing type data.
 * @param ValidatorInterface $validator The validator for validating the card data.
 *
 * @return JsonResponse A JSON response with HTTP status 201 (Created) and the created card data.
 * The Location header in the response will contain the absolute URL of the detail view of the created card.
 *
 * @throws Exception If the request content cannot be deserialized to a Card object.
 *
 * @Route("/api/cards", name="createCard", methods={"POST"})
 */
#[Route('/api/cards', name: 'createCard', methods: ['POST'])]
public function createCard(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
    UrlGeneratorInterface $urlGenerator, TypeRepository $typeRepository, ValidatorInterface $validator): JsonResponse {
    // Deserialize the request content to a Card object
    try {
        $card = $serializer->deserialize($request->getContent(), Card::class, 'json');
    } catch (Exception $e) {
        throw new Exception('Request content cannot be deserialized to a Card object.');
    }

    // Validate the card data
    $errors = $validator->validate($card);
    if ($errors->count() > 0) {
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }

    // Persist the new card in the entity manager
    $em->persist($card);
    
    // Flush the changes to the database
    $em->flush();

    // Extract the idType from the request content
    $content = $request->toArray();
    $idType = $content['idType']?? -1;

    // Set the type of the card using the idType
    $card->setType($typeRepository->find($idType));

    // Serialize the card object to JSON using the 'getCards' group
    $jsonCard = $serializer->serialize($card, 'json', ['groups' => 'getCards']);
    
    // Generate the absolute URL for the detail view of the created card
    $location = $urlGenerator->generate('detailCard', ['id' => $card->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

    // Return the JSON response with HTTP status 201 (Created) and the Location header set to the detail view URL
    return new JsonResponse($jsonCard, Response::HTTP_CREATED, ["Location" => $location], true);
}
    
    
    /**
 * This function updates a card in the database based on the provided request data.
 *
 * @param Request $request The request object containing the updated card data.
 * @param SerializerInterface $serializer The serializer for converting objects to JSON.
 * @param Card $currentCard The card entity to update.
 * @param EntityManagerInterface $em The entity manager for database operations.
 * @param TypeRepository $typeRepository The repository for accessing type data.
 * @param ValidatorInterface $validator The validator for validating the card data.
 *
 * @return JsonResponse A JSON response with HTTP status 204 (No Content) if the update is successful.
 *
 * @throws Exception If the request content cannot be deserialized to a Card object.
 *
 * @Route("/api/cards/{id}", name="updateCard", methods={"PUT"})
 */
#[Route('/api/cards/{id}', name: 'updateCard', methods: ['PUT'])]
public function updateCard(Request $request, SerializerInterface $serializer,Card $currentCard, EntityManagerInterface $em, TypeRepository $typeRepository, ValidatorInterface $validator): JsonResponse {
    // Deserialize the request content to a Card object, populating the existing $currentCard object
    try {
        $updatedCard = $serializer->deserialize($request->getContent(), Card::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCard]);
    } catch (Exception $e) {
        throw new Exception('Request content cannot be deserialized to a Card object.');
    }

    // Validate the card data
    $errors = $validator->validate($updatedCard);
    if ($errors->count() > 0) {
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }

    // Extract the idType from the request content
    $content = $request->toArray();
    $idType = $content['idType']?? -1;

    // Set the type of the card using the idType
    $updatedCard->setType($typeRepository->find($idType));

    // Persist the updated card in the entity manager
    $em->persist($updatedCard);
    
    // Flush the changes to the database
    $em->flush();

    // Return a JSON response with HTTP status 204 (No Content)
    return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
}


}