<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Ingredient;
use Doctrine\ORM\EntityManagerInterface;

class IngredientController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $ingredient = new Ingredient();
        $ingredient->setNom($data['nom_ingredient']);
        $ingredient->setDescription($data['description']);
        $ingredient->setRisqueSeul($data['risque_seul']);

        $this->entityManager->persist($ingredient);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Ingrédient créé avec succès'], JsonResponse::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ingredient = $this->entityManager->getRepository(Ingredient::class)->find($id);

        if (!$ingredient) {
            return new JsonResponse(['message' => 'Ingrédient non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $ingredient->setNom($data['nom_ingredient'] ?? $ingredient->getNom());
        $ingredient->setDescription($data['description'] ?? $ingredient->getDescription());
        $ingredient->setRisqueSeul($data['risque_seul'] ?? $ingredient->getRisqueSeul());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Ingrédient mis à jour avec succès'], JsonResponse::HTTP_OK);
    }

    public function getAll(): JsonResponse
    {
        $ingredients = $this->entityManager->getRepository(Ingredient::class)->findAll();
        $ingredientsArray = [];

        foreach ($ingredients as $ingredient) {
            $ingredientsArray[] = [
                'id' => $ingredient->getId(),
                'nom_ingredient' => $ingredient->getNom(),
                'description' => $ingredient->getDescription(),
                'risque_seul' => $ingredient->getRisqueSeul()
            ];
        }

        return new JsonResponse($ingredientsArray, JsonResponse::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $ingredient = $this->entityManager->getRepository(Ingredient::class)->find($id);

        if (!$ingredient) {
            return new JsonResponse(['message' => 'Ingrédient non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($ingredient);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Ingrédient supprimé avec succès'], JsonResponse::HTTP_OK);
    }
}
