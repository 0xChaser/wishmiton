<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/category')]
#[OA\Tag(name: 'Category')]
class CategoryController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    // GET ALL CATEGORIES
    #[Route('', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of categories',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'count', type: 'integer'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function getAllCategories(): JsonResponse
    {
        try {
            $categories = $this->entityManager->getRepository(Category::class)->findAll();

            $data = array_map(function (Category $category) {
                return [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ];
            }, $categories);

            return new JsonResponse([
                'success' => true,
                'count' => count($data),
                'data' => $data
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving categories: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // GET ONE CATEGORY
    #[Route('/{id}', methods: ['GET'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Category ID to retrieve',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful category retrieval',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
            ]
        )   
    )]
    #[OA\Response(
        response: 404,
        description: 'Category not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
            ],
            type: 'object'
        )
    )]
    public function getCategoryById(int $id): JsonResponse
    {
        try {
            $category = $this->entityManager->getRepository(Category::class)->find($id);

            if (!$category) {
                return $this->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'id' => $category->getId(),
                'name' =>  $category->getName(),
            ];

            return $this->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error retrieving category: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // CREATE A CATEGORY
    #[Route('', methods: ['POST'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Category created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                    ],
                    type: 'object'
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - Missing required fields',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function createCategory(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            

            $category = new Category();
            $category->setName($data['name']);

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'category created successfully',
                'data' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error creating category: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // UPDATE A CATEGORY
    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Category ID to retrieve',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Category updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string'),
                ])
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Category not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function updateCategory(Request $request, int $id): JsonResponse
    {
        try {
            $category = $this->entityManager->getRepository(Category::class)->find($id);
    
            if (!$category) {
                return $this->json(['message' => 'category not found'], Response::HTTP_NOT_FOUND);
            }
    
            $data = json_decode($request->getContent(), true);
    
            if (isset($data['name'])) {
                $category->setName($data['name']);
            }
    
            $this->entityManager->flush();
    
            return $this->json([
                'message' => 'category updated successfully',
                'data' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ]
            ]);
    
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error updating category: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // DELETE A CATEGORY
    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Category successfully deleted',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Category not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function deleteCategory(int $id): JsonResponse
    {
        try {
            $category = $this->entityManager->getRepository(Category::class)->find($id);

            if (!$category) {
                return $this->json(['message' => 'category not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($category);
            $this->entityManager->flush();

            return $this->json(['message' => 'category deleted successfully']);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // DELETE ALL CATEGORIES
    #[Route('', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'All categories deleted successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function deleteAllCategories(): JsonResponse
    {
        try {
            $categories = $this->entityManager->getRepository(Category::class)->findAll();
            foreach ($categories as $category) {
                $this->entityManager->remove($category);
            }
            $this->entityManager->flush();

            return $this->json(['message' => 'All categories deleted successfully']);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error deleting categories: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
