<?php

namespace App\Repository;

use App\Entity\AuthToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class AuthTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthToken::class);
    }

    public function findValidToken(string $token): ?AuthToken
    {
        return $this->createQueryBuilder('t')
            ->where('t.token = :token')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteExpiredTokens(): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt <= :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }

    public function save(AuthToken $token): void
    {
        $em = $this->getEntityManager();
        $em->persist($token);
        $em->flush();
    }

    public function remove(AuthToken $token): void
    {
        $em = $this->getEntityManager();
        $em->remove($token);
        $em->flush();
    }

    public function getUserByToken(Request $request) : User
    {
        $token = explode(' ',$request->headers->get('Authorization'))[1];
        $result = $this->findValidToken($token);
        $user = $result->getUser();
        return $user;
    }
}
