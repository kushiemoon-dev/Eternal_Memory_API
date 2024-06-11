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
         
         *
         * @param Type $type
         * @param SerializerInterface $serializer
         * @return JsonResponse
         */
        #[Route('/api/types/{id}', name: 'detailType', methods: ['GET'])]
        public function getDetailType(Type $type, SerializerInterface $serializer): JsonResponse {
            $jsonType = $serializer->serialize($type, 'json', ['groups' => 'getTypes']);
            return new JsonResponse($jsonType, Response::HTTP_OK, [], true);
        }
    
        
        /**
         
         * 
         * @param Type $type
         * @param EntityManagerInterface $em
         * @return JsonResponse
         */
        #[Route('/api/types/{id}', name: 'deleteType', methods: ['DELETE'])]
        public function deleteType(Type $type, EntityManagerInterface $em): JsonResponse {
            
            $em->remove($type);
            
            $em->flush();

              
        /**
         * 
         * dd($type->getCards());
         */
        
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    
        /**
        
         *
         * @param Request $request
         * @param SerializerInterface $serializer
         * @param EntityManagerInterface $em
         * @param UrlGeneratorInterface $urlGenerator
         * @return JsonResponse
         */
        #[Route('/api/types', name: 'createType', methods: ['POST'])]
        public function createType(Request $request, SerializerInterface $serializer,
            EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse {
            $type = $serializer->deserialize($request->getContent(), Type::class, 'json');
            $em->persist($type);
            $em->flush();
    
            $jsonType = $serializer->serialize($type, 'json', ['groups' => 'getTypes']);
            $location = $urlGenerator->generate('detailType', ['id' => $type->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            return new JsonResponse($jsonType, Response::HTTP_CREATED, ["Location" => $location], true);	
        }
    
        
        /**
         
         * 
         * @param Request $request
         * @param SerializerInterface $serializer
         * @param Type $currentType
         * @param EntityManagerInterface $em
         * @return JsonResponse
         */
        #[Route('/api/types/{id}', name:"updateType", methods:['PUT'])]
        public function updateType(Request $request, SerializerInterface $serializer,
            Type $currentType, EntityManagerInterface $em): JsonResponse {
    
            $updatedType = $serializer->deserialize($request->getContent(), Type::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentType]);
            $em->persist($updatedType);
            $em->flush();
    
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    
        }
}