<?php

namespace App\Controller;

use App\Entity\Card;
use App\Repository\TypeRepository;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CardController extends AbstractController
{   
    /**
 * Retrieves a paginated list of Card entities.
 *
 * @Route("/api/cards", name="cards", methods={"GET"})
 *
 * @param CardRepository $cardRepository The repository for Card entities.
 * @param SerializerInterface $serializer The serializer service to convert the Card entities to JSON.
 * @param Request $request The request object to get pagination parameters.
 * @param TagAwareCacheInterface $cache The cache service to cache the response.
 *
 * @return JsonResponse A JSON response containing the paginated list of Card entities.
 */
#[Route('/api/cards', name: 'cards', methods: ['GET'])]
public function getAllCards(CardRepository $cardRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
{
    // Get the pagination parameters from the request
    $page = $request->get('page', 1);
    $limit = $request->get('limit', 3);

    // Generate a unique cache key based on the pagination parameters
    $idCache = "getAllCard-". $page. "-". $limit;
    
    // Get the paginated list of Card entities from the cache or the database
    $jsonCardList = $cache->get($idCache, function (ItemInterface $item) use ($cardRepository, $page, $limit, $serializer) {
        // Tag the cache item with "cardsCache"
        $item->tag("cardsCache");
        
        // Retrieve the paginated list of Card entities from the database
        $cardList = $cardRepository->findAllWithPagination($page, $limit);
        
        // Serialize the paginated list of Card entities to JSON
        return $serializer->serialize($cardList, 'json', ['groups' => 'getCards']);
    });

    // Return a JSON response with the paginated list of Card entities
    return new JsonResponse($jsonCardList, Response::HTTP_OK, [], true);
}

    /**
 * Clears the cache for all Card entities.
 *
 * @Route("/api/cards/clearCache", name="clearCache", methods={"GET"})
 *
 * @param TagAwareCacheInterface $cache The cache service to invalidate the tags.
 *
 * @return JsonResponse A JSON response with a message indicating that the cache has been cleared and HTTP_OK status.
 */
#[Route('/api/cards/clearCache', name:"clearCache", methods:['GET'])]
public function clearCache(TagAwareCacheInterface $cache) {
    // Invalidate the cache for the "cardsCache" tag
    $cache->invalidateTags(["cardsCache"]);

    // Return a JSON response with a message indicating that the cache has been cleared and HTTP_OK status
    return new JsonResponse("Cache clear", JsonResponse::HTTP_OK);
}

    /**
 * Retrieves a specific Card entity by its ID.
 *
 * @Route("/api/cards/{id}", name="detailCard", methods={"GET"})
 *
 * @param Card $card The Card entity to retrieve.
 * @param SerializerInterface $serializer The serializer service to convert the Card entity to JSON.
 *
 * @return JsonResponse A JSON response containing the serialized Card entity with HTTP_OK status.
 */
#[Route('/api/cards/{id}', name: 'detailCard', methods: ['GET'])]
public function getDetailCard(Card $card, SerializerInterface $serializer): JsonResponse {
    // Serialize the Card entity to JSON using the 'getCards' group
    $jsonCard = $serializer->serialize($card, 'json', ['groups' => 'getCards']);

    // Return a JSON response with the serialized Card entity and HTTP_OK status
    return new JsonResponse($jsonCard, Response::HTTP_OK, [], true);
}
      
    /**
 * Deletes a Card entity.
 *
 * @Route("/api/cards/{id}", name="deleteCard", methods={"DELETE"})
 * @IsGranted("ROLE_ADMIN", message="you do not have the necessary rights to delete a card")
 *
 * @param Card $card The Card entity to be deleted.
 * @param EntityManagerInterface $em The entity manager service.
 * @param TagAwareCacheInterface $cache The cache service.
 *
 * @return JsonResponse A JSON response with HTTP_NO_CONTENT status if the deletion is successful.
 */
#[Route('/api/cards/{id}', name: 'deleteCard', methods: ['DELETE'])]
#[IsGranted('ROLE_ADMIN', message: 'you do not have the necessary rights to delete a card')]
public function deleteCard(Card $card, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse {

    $cachePool->invalidateTags(["cardsCache"]);
    $em->remove($card);
    $em->flush();
    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}

    /**
 * Creates a new Card entity.
 *
 * @Route("/api/cards", name="createCard", methods={"POST"})
 * @IsGranted("ROLE_ADMIN", message="you do not have the necessary rights to create a card")
 *
 * @param Request $request The request object.
 * @param SerializerInterface $serializer The serializer service.
 * @param EntityManagerInterface $em The entity manager service.
 * @param UrlGeneratorInterface $urlGenerator The URL generator service.
 * @param TypeRepository $typeRepository The Type repository service.
 * @param ValidatorInterface $validator The validator service.
 * @param TagAwareCacheInterface $cache The cache service.
 *
 * @return JsonResponse A JSON response with HTTP_CREATED status if the creation is successful.
 * @return JsonResponse A JSON response with HTTP_BAD_REQUEST status if the request is not valid.
 */
#[Route('/api/cards', name:"createCard", methods: ['POST'])]
#[IsGranted('ROLE_ADMIN', message: 'you do not have the necessary rights to create a card')]
public function createCard(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
    UrlGeneratorInterface $urlGenerator, TypeRepository $typeRepository, ValidatorInterface $validator,
    TagAwareCacheInterface $cache): JsonResponse {

    // Deserialize the request content to a Card entity
    $card = $serializer->deserialize($request->getContent(), Card::class, 'json');

    // Validate the Card entity
    $errors = $validator->validate($card);
    if ($errors->count() > 0) {
        // Return a JSON response with HTTP_BAD_REQUEST status if the request is not valid
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }

    // Get the idType from the request content
    $content = $request->toArray();
    $idType = $content['idType']?? -1;

    // Set the Type of the Card entity using the idType
    $card->setType($typeRepository->find($idType));

    // Persist the Card entity in the database
    $em->persist($card);
    $em->flush();

    // Invalidate the cache for the "cardsCache" tag
    $cache->invalidateTags(["cardsCache"]);

    // Serialize the Card entity to JSON
    $jsonCard = $serializer->serialize($card, 'json', ['groups' => 'getCards']);

    // Generate the URL for the detailCard route with the id of the created Card entity
    $location = $urlGenerator->generate('detailCard', ['id' => $card->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

    // Return a JSON response with HTTP_CREATED status and the Location header set to the detailCard URL
    return new JsonResponse($jsonCard, Response::HTTP_CREATED, ["Location" => $location], true);
}
    
    
    /**
 * Updates an existing Card entity.
 *
 * @Route("/api/cards/{id}", name="updateCard", methods={"PUT"})
 * @IsGranted("ROLE_ADMIN", message="you do not have the necessary rights to update a card")
 *
 * @param Request $request The request object.
 * @param SerializerInterface $serializer The serializer service.
 * @param Card $currentCard The current Card entity to be updated.
 * @param EntityManagerInterface $em The entity manager service.
 * @param TypeRepository $typeRepository The Type repository service.
 * @param ValidatorInterface $validator The validator service.
 * @param TagAwareCacheInterface $cache The cache service.
 *
 * @return JsonResponse A JSON response with HTTP_NO_CONTENT status if the update is successful.
 * @return JsonResponse A JSON response with HTTP_BAD_REQUEST status if the request is not valid.
 */
#[Route('/api/cards/{id}', name:"updateCard", methods:['PUT'])]
#[IsGranted('ROLE_ADMIN', message: 'you do not have the necessary rights to update a card')]
public function updateCard(Request $request, SerializerInterface $serializer,
                        Card $currentCard, EntityManagerInterface $em, TypeRepository $typeRepository, 
                        ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse {
    $updatedCard = $serializer->deserialize($request->getContent(), Card::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCard]);

    $errors = $validator->validate($updatedCard);
    if ($errors->count() > 0) {
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }

    $content = $request->toArray();
    $idType = $content['idType']?? -1;

    $updatedCard->setType($typeRepository->find($idType));

    $em->persist($updatedCard);
    $em->flush();

    $cache->invalidateTags(["cardsCache"]);

    return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
}


}