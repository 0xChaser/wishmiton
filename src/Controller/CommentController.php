<?php

namespace App\Controller;

use App\Entity\AuthToken;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use PhpParser\Node\Stmt\TryCatch;

#[Route('/api/comments')]
#[OA\Tag(name: 'Comments')]
class CommentController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // GET ALL COMMENTS
    #[Route('', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of comments',
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
                            new OA\Property(property: 'content', type: 'string'),
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
    public function getAllComments(): JsonResponse
    {
        try {
            $comments = $this->entityManager->getRepository(Comment::class)->findAll();

            $data = array_map(function (Comment $category) {
                return [
                    'id' => $category->getId(),
                    'title' => $category->getTitle(),
                    'content' => $category->getContent(),
                ];
            }, $comments);

            return new JsonResponse([
                'success' => true,
                'count' => count($data),
                'data' => $data
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving comments: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // GET ONE COMMENT
    #[Route('/{id}', methods: ['GET'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Retrieve one comment by his ID',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful comment retrieval',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'content', type: 'string'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Comment not found',
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
    public function getCommentById(int $id): JsonResponse
    {
        try {
            $comment = $this->entityManager->getRepository(Comment::class)->find($id);

            if (!$comment) {
                return $this->json(['message' => 'comment not found'], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'id' => $comment->getId(),
                'title' =>  $comment->getTitle(),
                'content' => $comment->getContent(),
            ];

            return $this->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error retrieving comment: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // POST A COMMENT
    #[Route('', methods: ['POST'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title', 'content'],
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'content', type: 'text'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Comment created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'title', type: 'string'),
                        new OA\Property(property: 'content', type: 'text')
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - Missing required fields',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    public function createComment(Request $request): JsonResponse
    {
        try {

            $data = json_decode($request->getContent(), true);
            $result = $this->entityManager->getRepository(AuthToken::class)->verifyToken($request);
            $user = $result['user'];

            $comment = new Comment();
            $comment->setTitle($data['title']);
            $comment->setContent($data['content']);
            $comment->setAuthor($user);

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Comment created successfully',
                'data' => [
                    'id' => $comment->getId(),
                    'title' => $comment->getTitle(),
                    'content' => $comment->getContent()
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error creating comment: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // UPDATE A COMMENT
    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Comment ID to retrieve',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'content', type: 'text')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Comment updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'title', type: 'string'),
                        new OA\Property(property: 'content', type: 'text')
                    ]
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Comment not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    public function updateComment(Request $request, int $id): JsonResponse
    {
        try {
            $comment = $this->entityManager->getRepository(Comment::class)->find($id);
            $result = $this->entityManager->getRepository(AuthToken::class)->verifyToken($request);
            $user = $result['user'];

            if (!$comment) {
                return $this->json(
                    ['message' => 'Comment not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            if ($comment->getAuthor() == $user) {
                $data = json_decode($request->getContent(), true);

                if (isset($data['title'])) {
                    $comment->setTitle($data['title']);
                }
                if (isset($data['content'])) {
                    $comment->setContent($data['content']);
                }

                $this->entityManager->flush();

                return $this->json([
                    'message' => 'Comment successfully updated',
                    'data' => [
                        'id' => $comment->getId(),
                        'title' => $comment->getTitle(),
                        'content' => $comment->getContent(),
                    ]
                ]);
            } else {
                return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error updating recipe: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // DELETE A COMMENT
    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Comment successfully deleted',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Comment not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    public function deleteComment(int $id, Request $request): JsonResponse
    {
        try {
            $comment = $this->entityManager->getRepository(Comment::class)->find($id);
            $result = $this->entityManager->getRepository(AuthToken::class)->verifyToken($request);
            $user = $result['user'];

            if (!$comment) {
                return $this->json(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
            }
            if ($comment->getAuthor() == $user || $user->isAdmin()) {
                $this->entityManager->remove($comment);
                $this->entityManager->flush();

                return $this->json(['message' => 'Comment deleted successfully']);
            } else {
                return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error deleting comment: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // DELETE ALL COMMENTS
    #[Route('', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'All comments deleted successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
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
    #[OA\Response(
        response: 403,
        description: 'Forbidden',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ],
            type: 'object'
        )
    )]
    public function deleteAllComments(Request $request): JsonResponse
    {
        try {
            $comments = $this->entityManager->getRepository(Comment::class)->findAll();
            $result = $this->entityManager->getRepository(AuthToken::class)->verifyToken($request);
            $user = $result['user'];

            if ($user->isAdmin()) {
                foreach ($comments as $comment) {
                    $this->entityManager->remove($comment);
                }
                $this->entityManager->flush();

                return $this->json(['message' => 'All comments successfully deleted']);
            } else {
                return $this->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
            }
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error deleting comments: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
