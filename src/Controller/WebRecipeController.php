<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebRecipeController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        try {
            $response = $this->client->request('GET', '/api/recipe', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getUser()?->getApiToken(),
                ]
            ]);

            $result = json_decode($response->getContent(), true);
            $recipes = $result['data'] ?? [];


            $featuredRecipes = array_slice($recipes, 0, 3);

            return $this->render('home/index.html.twig', [
                'categories' => $categories,
                'featuredRecipes' => $featuredRecipes,
                'recipes' => $recipes,
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération des recettes.');
            return $this->render('home/index.html.twig', [
                'categories' => [],
                'featuredRecipes' => [],
                'recipes' => [],
            ]);
        }
    }

    #[Route('/recipe/{id}', name: 'recipe_show')]
    public function show(int $id): Response
    {
        try {
            $response = $this->client->request('GET', "/api/recipe/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getUser()?->getApiToken(),
                ]
            ]);

            if ($response->getStatusCode() === 404) {
                throw $this->createNotFoundException('Cette recette n\'existe pas.');
            }

            $recipe = json_decode($response->getContent(), true);

            return $this->render('recipe/show.html.twig', [
                'recipe' => $recipe,
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération de la recette.');
            return $this->redirectToRoute('home');
        }
    }

    #[Route('/my-recipes', name: 'my_recipes')]
    public function myRecipes(): Response
    {
        try {
            $response = $this->client->request('GET', '/api/recipe', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getUser()?->getApiToken(),
                ]
            ]);

            $result = json_decode($response->getContent(), true);
            $recipes = $result['data'] ?? [];

            $myRecipes = array_filter($recipes, function($recipe) {
                return $recipe['user_id'] === $this->getUser()->getId();
            });

            return $this->render('recipe/my_recipes.html.twig', [
                'recipes' => $myRecipes,
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération de vos recettes.');
            return $this->redirectToRoute('home');
        }
    }

    #[Route('/recipe/new', name: 'recipe_new')]
    public function new(): Response
    {
        return $this->render('recipe/new.html.twig');
    }

    #[Route('/recipe/edit/{id}', name: 'recipe_edit')]
    public function edit(int $id): Response
    {
        try {
            $response = $this->client->request('GET', "/api/recipe/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getUser()?->getApiToken(),
                ]
            ]);

            if ($response->getStatusCode() === 404) {
                throw $this->createNotFoundException('Cette recette n\'existe pas.');
            }

            $recipe = json_decode($response->getContent(), true);

            return $this->render('recipe/edit.html.twig', [
                'recipe' => $recipe,
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération de la recette.');
            return $this->redirectToRoute('my_recipes');
        }
    }
}