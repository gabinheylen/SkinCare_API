<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;
use App\Entity\Ingredient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProduitController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, LoggerInterface $logger): JsonResponse
    {
        $logger->info('Start creating product');

        $produit = new Produit();
        $produit->setNom($request->request->get('nom'));
        $produit->setMarque($request->request->get('marque'));
        $produit->setDescription($request->request->get('description'));
        $produit->setCode($request->request->get('code'));

        $details = $request->request->get('details');
        if (is_string($details)) {
            $details = json_decode($details, true);
        }
        $produit->setDetails($details);

        $logger->info('Validation successful');

        // Handle file uploads
        $images = $request->files->get('images', []);
        if (!is_array($images)) {
            $images = [$images]; // Make sure $images is always an array
        }

        $paths = [];
        foreach ($images as $image) {
            if (!$image instanceof UploadedFile || !$image->isValid()) {
                $logger->error('Invalid file upload', ['error' => $image->getError()]);
                continue; // Skip invalid uploads
            }

            $filename = md5(uniqid()) . '.' . $image->guessExtension();
            $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/images/' . $filename;

            try {
                $image->move($this->getParameter('kernel.project_dir') . '/public/uploads/images', $filename);
                $logger->info('Image uploaded and saved', ['filename' => $filename, 'destination' => $destination]);
                $paths[] = '/uploads/images/' . $filename;
            } catch (\Exception $e) {
                $logger->error('Failed to save image', ['error' => $e->getMessage()]);
            }
        }

        if (empty($paths)) {
            $paths[] = '/uploads/images/default.jpg'; // Fallback to default image
        }

        $produit->setImages($paths);
        $entityManager->persist($produit);
        $entityManager->flush();

        $logger->info('Product created successfully', ['product_id' => $produit->getId()]);
        return new JsonResponse(['message' => 'Produit créé avec succès', 'product_id' => $produit->getId()], Response::HTTP_CREATED);
    }


    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $produit = $this->entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['message' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $produit->setNom($data['nom'] ?? $produit->getNom());
        $produit->setMarque($data['marque'] ?? $produit->getMarque());
        $produit->setDescription($data['description'] ?? $produit->getDescription());
        $produit->setDetails($data['details'] ?? $produit->getDetails());

        $images = $request->files->get('images');
        if ($images) {
            $paths = [];
            foreach ($images as $image) {
                $filename = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('kernel.project_dir') . '/public/uploads/images', $filename);
                $paths[] = '/uploads/images/' . $filename;
            }
            $produit->setImages(implode(";", $paths));
        } else {
            $produit->setImages($data['images'] ?? $produit->getImages());
        }

        // Mettre à jour les ingrédients
        if (isset($data['ingredients']) && is_array($data['ingredients'])) {
            $produit->getIngredients()->clear();
            foreach ($data['ingredients'] as $ingredientId) {
                $ingredient = $this->entityManager->getRepository(Ingredient::class)->find($ingredientId);
                if ($ingredient) {
                    $produit->addIngredient($ingredient);
                }
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Produit mis à jour avec succès'], JsonResponse::HTTP_OK);
    }

    public function getAll(): JsonResponse
    {
        $produits = $this->entityManager->getRepository(Produit::class)->findAll();
        $produitsArray = [];

        foreach ($produits as $produit) {
            $produitsArray[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'marque' => $produit->getMarque(),
                'code' => $produit->getCode(),
                'description' => $produit->getDescription(),
                'details' => $produit->getDetails(),
                'images' => array_map(function ($path) {
                    return $this->getParameter('host') . $path;
                }, $produit->getImages()),
                'ingredients' => array_map(function ($ingredient) {
                    return [
                        'id' => $ingredient->getId(),
                        'nom' => $ingredient->getNom()
                    ];
                }, $produit->getIngredients()->toArray())
            ];
        }

        return new JsonResponse($produitsArray, JsonResponse::HTTP_OK);
    }

    public function getById(int $id): JsonResponse
    {
        $produit = $this->entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['message' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $images = $produit->getImages();
        if (!is_array($images)) {
            $images = explode(";", $images);
        }

        $produitArray = [
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'marque' => $produit->getMarque(),
            'description' => $produit->getDescription(),
            'details' => $produit->getDetails(),
            'images' => array_map(function ($path) {
                return $this->getParameter('host') . $path;
            }, $images),
            'ingredients' => array_map(function ($ingredient) {
                return [
                    'id' => $ingredient->getId(),
                    'nom' => $ingredient->getNom()
                ];
            }, $produit->getIngredients()->toArray())
        ];

        return new JsonResponse($produitArray, JsonResponse::HTTP_OK);
    }

    public function getByCode(int $code): JsonResponse
    {
        $produit = $this->entityManager->getRepository(Produit::class)->findOneBy(['Code' => $code]);

        if (!$produit) {
            return new JsonResponse(['message' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $images = $produit->getImages();
        if (!is_array($images)) {
            $images = explode(";", $images);
        }

        $produitArray = [
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'marque' => $produit->getMarque(),
            'description' => $produit->getDescription(),
            'details' => $produit->getDetails(),
            'images' => array_map(function ($path) {
                return $this->getParameter('host') . $path;
            }, $images),
            'ingredients' => array_map(function ($ingredient) {
                return [
                    'id' => $ingredient->getId(),
                    'nom' => $ingredient->getNom()
                ];
            }, $produit->getIngredients()->toArray())
        ];

        return new JsonResponse($produitArray, JsonResponse::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $produit = $this->entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['message' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($produit);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Produit supprimé avec succès'], JsonResponse::HTTP_OK);
    }
}
