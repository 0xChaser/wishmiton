<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\AuthToken;
use App\Repository\AuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use OpenApi\Attributes as OA;

#[Route('/api/auth')]
#[OA\Tag(name: 'Authentification')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthTokenRepository $authTokenRepository
    ) {}

    #[Route('/login', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Credentials pour se connecter',
        required: true,
        content: new OA\JsonContent(
            required: ['pseudo', 'password'],
            properties: [
                new OA\Property(property: 'pseudo', type: 'string', example: 'johndoe'),
                new OA\Property(property: 'password', type: 'string', example: 'password123')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Login successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'token', type: 'string', example: 'Bearer'),
                new OA\Property(property: 'expiresAt', type: 'string', format: 'date-time'),
                new OA\Property(
                    property: 'user',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'pseudo', type: 'string'),
                        new OA\Property(property: 'isAdmin', type: 'boolean')
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials')
            ]
        )
    )]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['pseudo']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'Missing credentials'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['pseudo' => $data['pseudo']]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                return $this->json([
                    'message' => 'Invalid credentials'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $this->authTokenRepository->deleteExpiredTokens();

            $token = new AuthToken();
            $token->setUser($user);
            $token->setToken(bin2hex(random_bytes(32)));
            $token->setExpiresAt(new \DateTime('+1 hour'));

            $this->authTokenRepository->save($token);

            return $this->json([
                'token' => $token->getToken(),
                'expiresAt' => $token->getExpiresAt()->format(\DateTime::ISO8601),
                'user' => [
                    'id' => $user->getId(),
                    'pseudo' => $user->getPseudo(),
                    'isAdmin' => $user->isAdmin()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error during login: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/logout', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Logout successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Invalid token')
            ]
        )
    )]
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->headers->get('Authorization');
            if (!$token) {
                return $this->json([
                    'message' => 'Missing authorization token'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $token = str_replace('Bearer ', '', $token);
            $authToken = $this->authTokenRepository->findValidToken($token);

            if (!$authToken) {
                return $this->json([
                    'message' => 'Invalid token'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $this->entityManager->remove($authToken);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error during logout: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/verify', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Token is valid',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'valid', type: 'boolean', example: true),
                new OA\Property(
                    property: 'user',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'pseudo', type: 'string', example: 'johndoe'),
                        new OA\Property(property: 'isAdmin', type: 'boolean', example: false)
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid token',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'valid', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Invalid or expired token')
            ]
        )
    )]
    public function verifyToken(Request $request): JsonResponse
    {
        try {
            $token = $request->headers->get('Authorization');
            if (!$token) {
                return $this->json([
                    'valid' => false,
                    'message' => 'Missing authorization token'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $token = str_replace('Bearer ', '', $token);
            $authToken = $this->authTokenRepository->findValidToken($token);

            if (!$authToken) {
                return $this->json([
                    'valid' => false,
                    'message' => 'Invalid or expired token'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = $authToken->getUser();

            return $this->json([
                'valid' => true,
                'user' => [
                    'id' => $user->getId(),
                    'pseudo' => $user->getPseudo(),
                    'isAdmin' => $user->isAdmin()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'valid' => false,
                'message' => 'Error verifying token: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}