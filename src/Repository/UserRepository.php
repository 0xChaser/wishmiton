<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('user')
            ->select('user')
            ->orderBy('user.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function findOneById(int $id): ?User
    {
        return $this->createQueryBuilder('user')
            ->andWhere('user.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $pseudo
     * @return User|null
     */
    public function findOneByPseudo(string $pseudo): ?User
    {
        return $this->createQueryBuilder('user')
            ->andWhere('u.pseudo = :pseudo')
            ->setParameter('pseudo', $pseudo)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param User $user
     * @return void
     */
    public function save(User $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param User $user
     * @return void
     */
    public function remove(User $user): void
    {
        $this->_em->remove($user);
        $this->_em->flush();
    }

    /**
     * @return void
     */
    public function removeAll(): void
    {
        $this->createQueryBuilder('u')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
