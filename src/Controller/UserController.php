<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use OpenApi\Attributes as OA;

#[Route('/api/users')]
#[OA\Tag(name: 'Users')]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of users',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'count', type: 'integer'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'pseudo', type: 'string'),
                            new OA\Property(property: 'isAdmin', type: 'boolean'),
                            new OA\Property(property: 'recipeIds', type: 'array', items: new OA\Items(type: 'integer')),
                            new OA\Property(property: 'commentIds', type: 'array', items: new OA\Items(type: 'integer'))
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
    public function getAllUsers(): JsonResponse
    {
        try {
            $users = $this->entityManager->getRepository(User::class)->findAll();
            
            $data = [];
            foreach ($users as $user) {
                $data[] = [
                    'id' => $user->getId(),
                    'pseudo' => $user->getPseudo(),
                    'isAdmin' => $user->isAdmin(),
                    'recipeIds' => $user->getRecipeIds() ?? [],
                    'commentIds' => $user->getCommentIds() ?? []
                ];
            } 

            return new JsonResponse([
                'success' => true,
                'count' => count($data),
                'data' => $data
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving users: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'User ID to retrieve',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful user retrieval',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'pseudo', type: 'string'),
                new OA\Property(property: 'isAdmin', type: 'boolean'),
                new OA\Property(property: 'recipeIds', type: 'array', items: new OA\Items(type: 'integer')),
                new OA\Property(property: 'commentIds', type: 'array', items: new OA\Items(type: 'integer')),
            ],
            type: 'object'
        )   
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
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
    public function getUserById(int $id): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);
    
            if (!$user) {
                return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
    
            $data = [
                'id' => $user->getId(),
                'pseudo' => $user->getPseudo(),
                'isAdmin' => $user->isAdmin(),
                'recipeIds' => $user->getRecipeIds(),
                'commentIds' => $user->getCommentIds(),
            ];
    
            return $this->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error retrieving user: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    #[Route('', methods: ['POST'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['pseudo', 'password'],
            properties: [
                new OA\Property(property: 'pseudo', type: 'string', example: 'johndoe'),
                new OA\Property(property: 'password', type: 'string', example: 'password123'),
                new OA\Property(property: 'isAdmin', type: 'boolean', example: false),
                new OA\Property(property: 'recipeIds', type: 'array', items: new OA\Items(type: 'integer')),
                new OA\Property(property: 'commentIds', type: 'array', items: new OA\Items(type: 'integer'))
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'pseudo', type: 'string'),
                        new OA\Property(property: 'isAdmin', type: 'boolean'),
                        new OA\Property(property: 'recipeIds', type: 'array', items: new OA\Items(type: 'integer')),
                        new OA\Property(property: 'commentIds', type: 'array', items: new OA\Items(type: 'integer'))
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
    public function createUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['pseudo']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'Pseudo and password are required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['pseudo' => $data['pseudo']]);
            if ($existingUser) {
                return $this->json([
                    'message' => 'Pseudo already exists'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = new User();
            $user->setPseudo($data['pseudo']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
            
            $user->setIsAdmin($data['isAdmin'] ?? false);
            $user->setRecipeIds($data['recipeIds'] ?? []);
            $user->setCommentIds($data['commentIds'] ?? []);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->getId(),
                    'pseudo' => $user->getPseudo(),
                    'isAdmin' => $user->isAdmin(),
                    'recipeIds' => $user->getRecipeIds(),
                    'commentIds' => $user->getCommentIds()
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error creating user: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'pseudo', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'isAdmin', type: 'boolean'),
                new OA\Property(property: 'recipeIds', type: 'array', items: new OA\Items(type: 'integer')),
                new OA\Property(property: 'commentIds', type: 'array', items: new OA\Items(type: 'integer'))
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'User updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function updateUser(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);

            if (!$user) {
                return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['pseudo'])) {
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['pseudo' => $data['pseudo']]);
                if ($existingUser && $existingUser->getId() !== $id) {
                    return $this->json([
                        'message' => 'Pseudo already exists'
                    ], Response::HTTP_BAD_REQUEST);
                }
                $user->setPseudo($data['pseudo']);
            }
            if (isset($data['password'])) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
                $user->setPassword($hashedPassword);
            }
            if (isset($data['isAdmin'])) {
                $user->setIsAdmin($data['isAdmin']);
            }
            if (isset($data['recipeIds'])) {
                $user->setRecipeIds($data['recipeIds']);
            }
            if (isset($data['commentIds'])) {
                $user->setCommentIds($data['commentIds']);
            }

            $this->entityManager->flush();

            return $this->json([
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->getId(),
                    'pseudo' => $user->getPseudo(),
                    'isAdmin' => $user->isAdmin(),
                    'recipeIds' => $user->getRecipeIds(),
                    'commentIds' => $user->getCommentIds()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error updating user: ' . $e->getMessage()
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
    #[OA\Response(
        response: 200,
        description: 'User deleted successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function deleteUser(int $id): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);

            if (!$user) {
                return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return $this->json(['message' => 'User deleted successfully']);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'All users deleted successfully',
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
    public function deleteAllUsers(): JsonResponse
    {
        try {
            $users = $this->entityManager->getRepository(User::class)->findAll();
            foreach ($users as $user) {
                $this->entityManager->remove($user);
            }
            $this->entityManager->flush();

            return $this->json(['message' => 'All users deleted successfully']);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error deleting users: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}