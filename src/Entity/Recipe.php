<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $title = null;

    #[ORM\Column(type: 'json')]
    private array $ingredients = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'blob', nullable: true)]
    private $imageRecipe = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $cookingTime = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $preparationTime = null;

    #[ORM\Column(type: 'json')]
    private array $steps = [];

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $likeCount = 0;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $commentIds = [];

    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    public function setIngredients(array $ingredients): self
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImageRecipe()
    {
        return $this->imageRecipe;
    }

    public function setImageRecipe($imageRecipe): self
    {
        $this->imageRecipe = $imageRecipe;
        return $this;
    }

    public function getCookingTime(): ?int
    {
        return $this->cookingTime;
    }

    public function setCookingTime(?int $cookingTime): self
    {
        $this->cookingTime = $cookingTime;
        return $this;
    }

    public function getPreparationTime(): ?int
    {
        return $this->preparationTime;
    }

    public function setPreparationTime(?int $preparationTime): self
    {
        $this->preparationTime = $preparationTime;
        return $this;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function setSteps(array $steps): self
    {
        $this->steps = $steps;
        return $this;
    }

    public function getLikeCount(): int
    {
        return $this->likeCount;
    }

    public function setLikeCount(int $likeCount): self
    {
        $this->likeCount = $likeCount;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getCommentIds(): ?array
    {
        return $this->commentIds;
    }

    public function setCommentIds(?array $commentIds): self
    {
        $this->commentIds = $commentIds;
        return $this;
    }
}