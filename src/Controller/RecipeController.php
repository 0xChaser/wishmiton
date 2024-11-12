<?php

namespace App\Controller;

use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/recipe')]
#[OA\Tag(name: 'Recipe')]
class RecipeController extends AbstractController {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    #[Route('', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of recipes',
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
                            new OA\Property(property: 'title', type: 'string'),
                            new OA\Property(property: 'description', type: 'string'),
                            new OA\Property(property: 'category', type: 'string'),
                            new OA\Property(property: 'ingredients', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'steps', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'likeCount', type: 'integer'),
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
    public function getAllRecipes(): JsonResponse
    {
        try {
            $recipes = $this->entityManager->getRepository(Recipe::class)->findAll();

            $data = array_map(function (Recipe $recipe) {
                return [
                    'id' => $recipe->getId(),
                    'title' => $recipe->getTitle(),
                    'description' => $recipe->getDescription(),
                    'category' => $recipe->getCategory(),
                    'ingredients' => $recipe->getIngredients(),
                    'steps' => $recipe->getSteps(),
                    'likeCount' => $recipe->getLikeCount(),
                ];
            }, $recipes);

            return new JsonResponse([
                'success' => true,
                'count' => count($data),
                'data' => $data
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving recipes: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Recipe ID to retrieve',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful recipe retrieval',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'category', type: 'string'),
                new OA\Property(property: 'ingredients', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'steps', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'likeCount', type: 'integer'),
            ]
        )   
    )]
    #[OA\Response(
        response: 404,
        description: 'Recipe not found',
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
    public function getRecipeById(int $id): JsonResponse
    {
        try {
            $recipe = $this->entityManager->getRepository(Recipe::class)->find($id);

            if (!$recipe) {
                return $this->json(['message' => 'Recipe not found'], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'id' => $recipe->getId(),
                'title' => $recipe->getTitle(),
                'description' => $recipe->getDescription(),
                'category' => $recipe->getCategory(),
                'ingredients' => $recipe->getIngredients(),
                'steps' => $recipe->getSteps(),
                'likeCount' => $recipe->getLikeCount(),
            ];

            return $this->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error retrieving recipe: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', methods: ['POST'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title', 'description', 'category', 'ingredients', 'steps'],
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'category', type: 'string'),
                new OA\Property(property: 'ingredients', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'steps', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'likeCount', type: 'integer', example: 0)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Recipe created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'title', type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'category', type: 'string'),
                        new OA\Property(property: 'ingredients', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'steps', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'likeCount', type: 'integer', example: 0)
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
    public function createRecipe(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            

            $recipe = new Recipe();
            $recipe->setTitle($data['title']);
            $recipe->setDescription($data['description']);
            $recipe->setCategory($data['category']);
            $recipe->setIngredients($data['ingredients']);
            $recipe->setSteps($data['steps']);
            $recipe->setLikeCount($data['likeCount'] ?? 0);

            $this->entityManager->persist($recipe);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Recipe created successfully',
                'data' => [
                    'id' => $recipe->getId(),
                    'title' => $recipe->getTitle(),
                    'description' => $recipe->getDescription(),
                    'category' => $recipe->getCategory(),
                    'ingredients' => $recipe->getIngredients(),
                    'steps' => $recipe->getSteps(),
                    'likeCount' => $recipe->getLikeCount()
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error creating recipe: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Recipe ID to retrieve',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'category', type: 'string'),
                new OA\Property(property: 'ingredients', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'steps', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'likeCount', type: 'integer')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Recipe updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'category', type: 'string'),
                    new OA\Property(property: 'ingredients', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'steps', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'likeCount', type: 'integer')
                ])
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Recipe not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function updateRecipe(Request $request, int $id): JsonResponse
    {
        try {
            $recipe = $this->entityManager->getRepository(Recipe::class)->find($id);
    
            if (!$recipe) {
                return $this->json(['message' => 'Recipe not found'], Response::HTTP_NOT_FOUND);
            }
    
            $data = json_decode($request->getContent(), true);
    
            if (isset($data['title'])) {
                $recipe->setTitle($data['title']);
            }
            if (isset($data['description'])) {
                $recipe->setDescription($data['description']);
            }
            if (isset($data['category'])) {
                $recipe->setCategory($data['category']);
            }
            if (isset($data['ingredients'])) {
                $recipe->setIngredients($data['ingredients']);
            }
            if (isset($data['steps'])) {
                $recipe->setSteps($data['steps']);
            }
            if (isset($data['likeCount'])) {
                $recipe->setLikeCount($data['likeCount']);
            }
    
            $this->entityManager->flush();
    
            return $this->json([
                'message' => 'Recipe updated successfully',
                'data' => [
                    'id' => $recipe->getId(),
                    'title' => $recipe->getTitle(),
                    'description' => $recipe->getDescription(),
                    'category' => $recipe->getCategory(),
                    'ingredients' => $recipe->getIngredients(),
                    'steps' => $recipe->getSteps(),
                    'likeCount' => $recipe->getLikeCount()
                ]
            ]);
    
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error updating recipe: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    public function deleteRecipe(int $id): JsonResponse
    {
        try {
            $recipe = $this->entityManager->getRepository(Recipe::class)->find($id);

            if (!$recipe) {
                return $this->json(['message' => 'Recipe not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($recipe);
            $this->entityManager->flush();

            return $this->json(['message' => 'Recipe deleted successfully']);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error deleting recipe: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'All recipes deleted successfully',
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
    public function deleteAllRecipes(): JsonResponse
    {
        try {
            $recipes = $this->entityManager->getRepository(Recipe::class)->findAll();
            foreach ($recipes as $recipe) {
                $this->entityManager->remove($recipe);
            }
            $this->entityManager->flush();

            return $this->json(['message' => 'All recipes deleted successfully']);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error deleting recipes: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}