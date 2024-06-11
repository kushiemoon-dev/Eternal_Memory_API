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
     * 
     *
     *
     */
    #[Route('/api/types', name: 'types', methods: ['GET'])]
    public function getAllTypes(TypeRepository $typeRepository, SerializerInterface $serializer, 
        Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $idCache = "getAllType-" . $page . "-" . $limit;

        $jsonTypeList = $cache->get($idCache, function (ItemInterface $item) use ($typeRepository, $page, $limit, $serializer) {
            
            $item->tag("typesCache");
            $typeList = $typeRepository->findAllWithPagination($page, $limit);
            return $serializer->serialize($typeList, 'json', ['groups' => 'getTypes']);
        });
        
        return new JsonResponse($jsonTypeList, Response::HTTP_OK, [], true);
    }
	
    /**
     */
    #[Route('/api/types/{id}', name: 'detailType', methods: ['GET'])]
    public function getDetailType(Type $type, SerializerInterface $serializer): JsonResponse {
        $jsonType = $serializer->serialize($type, 'json', ['groups' => 'getTypes']);
        return new JsonResponse($jsonType, Response::HTTP_OK, [], true);
    }

    
    /**
     * 
     */
    #[Route('/api/types/{id}', name: 'deleteType', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'you do not have the necessary rights to delete a type')]
    public function deleteType(Type $type, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse {
        
        $cachePool->invalidateTags(["typesCache"]);
        $em->remove($type);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     *
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
        
        // On vide le cache. 
        $cache->invalidateTags(["typesCache"]);

        $jsonAuthor = $serializer->serialize($type, 'json', ['groups' => 'getTypes']);
        $location = $urlGenerator->generate('detailType', ['id' => $type->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ["Location" => $location], true);	
    }

    
    /**
     * 
     */
    #[Route('/api/types/{id}', name:"updateType", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'you do not have the necessary rights to update a type')]
    public function updateType(Request $request, SerializerInterface $serializer,
        Type $currentType, EntityManagerInterface $em, ValidatorInterface $validator,
        TagAwareCacheInterface $cache): JsonResponse {

        
        $errors = $validator->validate($currentType);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $updatedType = $serializer->deserialize($request->getContent(), Type::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentType]);
        $em->persist($updatedType);
        $em->flush();

        
        $cache->invalidateTags(["typesCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);

    }
}