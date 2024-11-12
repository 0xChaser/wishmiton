<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * @return Recipe[]
     */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('recipe')
            ->select('recipe')
            ->orderBy('recipe.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @return Recipe|null
     */
    public function findOneById(int $id): ?Recipe
    {
        return $this->createQueryBuilder('recipe')
            ->andWhere('recipe.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $category
     * @return Recipe[]
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('recipe')
            ->andWhere('recipe.category = :category')
            ->setParameter('category', $category)
            ->orderBy('recipe.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Recipe[]
     */
    public function findTopLikedRecipes(): array
    {
        return $this->createQueryBuilder('recipe')
            ->orderBy('recipe.likeCount', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Recipe $recipe
     * @return void
     */
    public function save(Recipe $recipe): void
    {
        $this->_em->persist($recipe);
        $this->_em->flush();
    }

    /**
     * @param Recipe $recipe
     * @return void
     */
    public function remove(Recipe $recipe): void
    {
        $this->_em->remove($recipe);
        $this->_em->flush();
    }

    /**
     * @return void
     */
    public function removeAll(): void
    {
        $this->createQueryBuilder('recipe')
            ->delete()
            ->getQuery()
            ->execute();
    }
}