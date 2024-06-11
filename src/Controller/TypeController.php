<?php

namespace App\Controller;

use App\Entity\Type;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class TypeController extends AbstractController
{
    
    /**
 * This function retrieves all types from the database with pagination.
 * It uses a cache system to store the results for a certain period of time.
 *
 * @Route("/api/types", name="types", methods={"GET"})
 * @param TypeRepository $typeRepository The repository for Type entity.
 * @param SerializerInterface $serializer The serializer to convert data to JSON.
 * @param Request $request The request object to get pagination parameters.
 * @param TagAwareCacheInterface $cache The cache system to store the results.
 * @return JsonResponse The JSON response containing the list of types.
 */
#[Route('/api/types', name: 'types', methods: ['GET'])]
public function getAllTypes(TypeRepository $typeRepository, SerializerInterface $serializer, 
    Request $request, TagAwareCacheInterface $cache): JsonResponse
{
    $page = $request->get('page', 1); // Get the page number from the request, default to 1 if not provided.
    $limit = $request->get('limit', 3); // Get the limit of items per page from the request, default to 3 if not provided.
    
    $idCache = "getAllType-". $page. "-". $limit; // Generate a unique cache ID based on the page and limit.

    // Try to get the result from the cache. If not found, execute the callback function.
    $jsonTypeList = $cache->get($idCache, function (ItemInterface $item) use ($typeRepository, $page, $limit, $serializer) {
        
        $item->tag("typesCache"); // Tag the cache item with "typesCache" tag.
        $typeList = $typeRepository->findAllWithPagination($page, $limit); // Retrieve the list of types with pagination.
        return $serializer->serialize($typeList, 'json', ['groups' => 'getTypes']); // Serialize the type list to JSON.
    });
    
    return new JsonResponse($jsonTypeList, Response::HTTP_OK, [], true); // Return the JSON response.
}
	
    /**
 * This function retrieves the details of a specific type from the database.
 *
 * @Route("/api/types/{id}", name="detailType", methods={"GET"})
 * @param Type $type The Type entity to retrieve details from.
 * @param SerializerInterface $serializer The serializer to convert data to JSON.
 * @return JsonResponse The JSON response containing the details of the type.
 * @throws Exception If the type entity is not found.
 */
#[Route('/api/types/{id}', name: 'detailType', methods: ['GET'])]
public function getDetailType(Type $type, SerializerInterface $serializer): JsonResponse {
    $jsonType = $serializer->serialize($type, 'json', ['groups' => 'getTypes']);
    return new JsonResponse($jsonType, Response::HTTP_OK, [], true);
}

    
    /**
 * This function deletes a specific type from the database.
 *
 * @Route("/api/types/{id}", name="deleteType", methods={"DELETE"})
 * @IsGranted("ROLE_ADMIN", message="you do not have the necessary rights to delete a type")
 *
 * @param Type $type The Type entity to delete.
 * @param EntityManagerInterface $em The EntityManager to handle database operations.
 * @param TagAwareCacheInterface $cachePool The cache system to invalidate the related cache tags.
 *
 * @return JsonResponse A JSON response with HTTP status code 204 (No Content) if the deletion is successful.
 */
#[Route('/api/types/{id}', name: 'deleteType', methods: ['DELETE'])]
#[IsGranted('ROLE_ADMIN', message: 'you do not have the necessary rights to delete a type')]
public function deleteType(Type $type, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse {
    
    // Invalidate the cache tags related to types.
    $cachePool->invalidateTags(["typesCache"]);
    
    // Remove the type from the database.
    $em->remove($type);
    
    // Flush the changes to the database.
    $em->flush();
    
    // Return a JSON response with HTTP status code 204 (No Content).
    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}

    /**
 * This function creates a new type in the database.
 *
 * @Route("/api/types", name="createType", methods={"POST"})
 * @IsGranted("ROLE_ADMIN", message="you do not have the necessary rights to create a type")
 *
 * @param Request $request The request object to get the JSON data for the new type.
 * @param SerializerInterface $serializer The serializer to convert data to and from JSON.
 * @param EntityManagerInterface $em The EntityManager to handle database operations.
 * @param UrlGeneratorInterface $urlGenerator The URL generator to create a URL for the new type.
 * @param ValidatorInterface $validator The validator to validate the new type.
 * @param TagAwareCacheInterface $cache The cache system to invalidate the related cache tags.
 *
 * @return JsonResponse A JSON response with HTTP status code 201 (Created) if the creation is successful.
 *                      The Location header contains the URL of the newly created type.
 *                      If the validation fails, a JSON response with HTTP status code 400 (Bad Request) is returned.
 */
#[Route('/api/types', name: 'createType', methods: ['POST'])]
#[IsGranted('ROLE_ADMIN', message: 'you do not have the necessary rights to create a type')]
public function createType(Request $request, SerializerInterface $serializer,
    EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator,
    TagAwareCacheInterface $cache): JsonResponse {
    $type = $serializer->deserialize($request->getContent(), Type::class, 'json');

    $errors = $validator->validate($type);
    if ($errors->count() > 0) {
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }

    $em->persist($type);
    $em->flush();

    // Invalidate the cache tags related to types.
    $cache->invalidateTags(["typesCache"]);

    $jsonAuthor = $serializer->serialize($type, 'json', ['groups' => 'getTypes']);
    $location = $urlGenerator->generate('detailType', ['id' => $type->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ["Location" => $location], true);
}

    
    /**
 * This function updates a specific type in the database.
 *
 * @Route("/api/types/{id}", name="updateType", methods={"PUT"})
 * @IsGranted("ROLE_ADMIN", message="you do not have the necessary rights to update a type")
 *
 * @param Request $request The request object to get the JSON data for the updated type.
 * @param SerializerInterface $serializer The serializer to convert data to and from JSON.
 * @param Type $currentType The Type entity to update.
 * @param EntityManagerInterface $em The EntityManager to handle database operations.
 * @param ValidatorInterface $validator The validator to validate the updated type.
 * @param TagAwareCacheInterface $cache The cache system to invalidate the related cache tags.
 *
 * @return JsonResponse A JSON response with HTTP status code 204 (No Content) if the update is successful.
 *                      If the validation fails, a JSON response with HTTP status code 400 (Bad Request) is returned.
 */
#[Route('/api/types/{id}', name: 'updateType', methods: ['PUT'])]
#[IsGranted('ROLE_ADMIN', message: 'you do not have the necessary rights to update a type')]
public function updateType(Request $request, SerializerInterface $serializer,
    Type $currentType, EntityManagerInterface $em, ValidatorInterface $validator,
    TagAwareCacheInterface $cache): JsonResponse {

    // Validate the current type.
    $errors = $validator->validate($currentType);
    if ($errors->count() > 0) {
        // If validation fails, return a JSON response with HTTP status code 400 (Bad Request) and the validation errors.
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }

    // Deserialize the updated type data from the request content and populate the current type entity.
    $updatedType = $serializer->deserialize($request->getContent(), Type::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentType]);

    // Persist the updated type entity in the database.
    $em->persist($updatedType);
    $em->flush();

    // Invalidate the cache tags related to types.
    $cache->invalidateTags(["typesCache"]);

    // Return a JSON response with HTTP status code 204 (No Content) to indicate successful update.
    return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
}
}