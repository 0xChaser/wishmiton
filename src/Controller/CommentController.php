<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/comments')]
#[OA\Tag(name: 'Comments')]
class CommentController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
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
        description: 'Comment ID to retrieve',
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

}
