<?php

namespace App\Controller;

use App\Entity\Type;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class TypeController extends AbstractController

    {
    
        /**
 * This function retrieves all Type entities from the database and returns them as a JSON response.
 *
 * @Route("/api/types", name="Type", methods={"GET"})
 *
 * @param TypeRepository $typeRepository The repository for Type entities.
 * @param SerializerInterface $serializer The serializer to convert entities to JSON.
 *
 * @return JsonResponse A JSON response containing the list of Type entities.
 */
#[Route('/api/types', name: 'Type', methods: ['GET'])]
public function getAllType(TypeRepository $typeRepository, SerializerInterface $serializer): JsonResponse
{
    // Fetch all Type entities from the database
    $typeList = $typeRepository->findAll();
    
    // Serialize the Type entities to JSON
    $jsonTypeList = $serializer->serialize($typeList, 'json', ['groups' => 'getTypes']);
    
    // Return the JSON response with HTTP status 200 (OK)
    return new JsonResponse($jsonTypeList, Response::HTTP_OK, [], true);
}
        
        /**
 * This function retrieves a single Type entity from the database based on its ID and returns it as a JSON response.
 *
 * @Route("/api/types/{id}", name="detailType", methods={"GET"})
 *
 * @param Type $type The Type entity to retrieve. This is automatically injected by Symfony's routing system.
 * @param SerializerInterface $serializer The serializer to convert the Type entity to JSON.
 *
 * @return JsonResponse A JSON response containing the details of the Type entity.
 *
 * @throws Exception If the Type entity with the given ID does not exist.
 */
#[Route('/api/types/{id}', name: 'detailType', methods: ['GET'])]
public function getDetailType(Type $type, SerializerInterface $serializer): JsonResponse {
    // Serialize the Type entity to JSON
    $jsonType = $serializer->serialize($type, 'json', ['groups' => 'getTypes']);
    
    // Return the JSON response with HTTP status 200 (OK)
    return new JsonResponse($jsonType, Response::HTTP_OK, [], true);
}
    
        
        /**
 * This function deletes a Type entity from the database based on its ID.
 *
 * @Route("/api/types/{id}", name="deleteType", methods={"DELETE"})
 *
 * @param Type $type The Type entity to delete. This is automatically injected by Symfony's routing system.
 * @param EntityManagerInterface $em The EntityManager to handle database operations.
 *
 * @return JsonResponse A JSON response with HTTP status 204 (No Content) if the deletion is successful.
 *
 * @throws Exception If the Type entity with the given ID does not exist.
 */
#[Route('/api/types/{id}', name: 'deleteType', methods: ['DELETE'])]
public function deleteType(Type $type, EntityManagerInterface $em): JsonResponse {
    // Remove the Type entity from the EntityManager
    $em->remove($type);
    
    // Flush the changes to the database
    $em->flush();

    // Debugging: Display the associated cards of the deleted Type entity
    // dd($type->getCards());

    // Return a JSON response with HTTP status 204 (No Content)
    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}
    
        /**
 * This function creates a new Type entity in the database.
 *
 * @Route("/api/types", name="createType", methods={"POST"})
 *
 * @param Request $request The incoming request containing the JSON data for the new Type entity.
 * @param SerializerInterface $serializer The serializer to convert the request data to a Type entity and vice versa.
 * @param EntityManagerInterface $em The EntityManager to handle database operations.
 * @param UrlGeneratorInterface $urlGenerator The URL generator to create a URL for the newly created Type entity.
 *
 * @return JsonResponse A JSON response with HTTP status 201 (Created) and the details of the newly created Type entity.
 * The Location header contains the URL of the newly created Type entity.
 */
#[Route('/api/types', name: 'createType', methods: ['POST'])]
public function createType(Request $request, SerializerInterface $serializer,
    EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse {
    // Deserialize the request content to a Type entity
    $type = $serializer->deserialize($request->getContent(), Type::class, 'json');
    
    // Persist the new Type entity in the EntityManager
    $em->persist($type);
    
    // Flush the changes to the database
    $em->flush();

    // Serialize the new Type entity to JSON
    $jsonType = $serializer->serialize($type, 'json', ['groups' => 'getTypes']);
    
    // Generate the URL for the newly created Type entity
    $location = $urlGenerator->generate('detailType', ['id' => $type->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    
    // Return the JSON response with HTTP status 201 (Created) and the Location header
    return new JsonResponse($jsonType, Response::HTTP_CREATED, ["Location" => $location], true);	
}
    
        
        /**
 * This function updates a Type entity in the database.
 *
 * @Route("/api/types/{id}", name="updateType", methods={"PUT"})
 *
 * @param Request $request The incoming request containing the JSON data for the updated Type entity.
 * @param SerializerInterface $serializer The serializer to convert the request data to a Type entity and vice versa.
 * @param Type $currentType The Type entity to update. This is automatically injected by Symfony's routing system.
 * @param EntityManagerInterface $em The EntityManager to handle database operations.
 *
 * @return JsonResponse A JSON response with HTTP status 204 (No Content) if the update is successful.
 *
 * @throws Exception If the Type entity with the given ID does not exist.
 */
#[Route('/api/types/{id}', name: 'updateType', methods: ['PUT'])]
public function updateType(Request $request, SerializerInterface $serializer,
    Type $currentType, EntityManagerInterface $em): JsonResponse {

    // Deserialize the request content to a Type entity, populating the existing entity with the new data
    $updatedType = $serializer->deserialize($request->getContent(), Type::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentType]);
    
    // Persist the updated Type entity in the EntityManager
    $em->persist($updatedType);
    
    // Flush the changes to the database
    $em->flush();

    // Return a JSON response with HTTP status 204 (No Content)
    return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
}
}