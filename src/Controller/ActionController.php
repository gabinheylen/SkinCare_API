<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\MesProduits;
use App\Entity\ProduitAimes;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Psr\Log\LoggerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;


class ActionController extends AbstractController
{
    private $logger;
    private $entityManager;
    private $jwtManager;


    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, JWTEncoderInterface $jwtManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
    }

    public function ajouterAMesProduits(int $produitId, Request $request): JsonResponse
    {
        $useremail = $this->getUserIdFromToken($request);
        if ($useremail->getStatusCode() == 200) {
            $data = json_decode($useremail->getContent(), true);
            $userEmail = $data['email'];

            // Utiliser l'ID utilisateur pour récupérer l'entité User correspondante
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        } else {
            return $useremail;
        }
        $produit = $this->entityManager->getRepository(Produit::class)->find($produitId);

        if (!$produit) {
            return new JsonResponse(['error' => 'Produit non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si le produit est déjà associé à l'utilisateur
        $existingMesProduits = $this->entityManager->getRepository(MesProduits::class)->findOneBy([
            'user' => $user,
            'produit' => $produit,
        ]);

        if ($existingMesProduits) {
            return new JsonResponse(['error' => 'Ce produit est déjà associé à vos produits'], Response::HTTP_BAD_REQUEST);
        }

        $mesProduits = new MesProduits();
        $mesProduits->setUser($user);
        $mesProduits->setProduit($produit);

        $this->entityManager->persist($mesProduits);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Produit ajouté à vos produits'], Response::HTTP_CREATED);
    }

    public function supprimerDeMesProduits(int $produitId, Request $request): JsonResponse
    {
        $useremail = $this->getUserIdFromToken($request);
        if ($useremail->getStatusCode() == 200) {
            $data = json_decode($useremail->getContent(), true);
            $userEmail = $data['email'];

            // Utiliser l'ID utilisateur pour récupérer l'entité User correspondante
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        } else {
            return $useremail;
        }
        $mesProduit = $this->entityManager->getRepository(MesProduits::class)->findOneBy(['produit' => $produitId, 'user' => $user]);

        if (!$mesProduit) {
            return new JsonResponse(['error' => 'Produit non trouvé dans vos produits'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($mesProduit);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Produit retiré de vos produits'], Response::HTTP_OK);
    }
    public function getMesProduits(Request $request): JsonResponse
    {
        $response = $this->getUserIdFromToken($request);
        if ($response->getStatusCode() !== 200) {
            return $response;  // Retourne l'erreur liée au token
        }

        $data = json_decode($response->getContent(), true);
        $userEmail = $data['email'];
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);

        $mesProduits = $this->entityManager->getRepository(MesProduits::class)->findBy(['user' => $user]);

        $produits = [];
        foreach ($mesProduits as $mesProduit) {
            $produit = $mesProduit->getProduit();
            $produits[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'images' => array_map(function ($imagePath) {
                    return $this->getParameter('host') . $imagePath; // Assurez-vous que 'host' est bien configuré dans parameters.yaml
                }, $produit->getImages()),
                'marque' => $produit->getMarque(),
                'code' => $produit->getCode(),
                'ingredients' => array_map(function ($ingredient) {
                    return [
                        'id' => $ingredient->getId(),
                        'nom' => $ingredient->getNom()
                    ];
                }, $produit->getIngredients()->toArray())
            ];
        }

        return new JsonResponse(['mesProduits' => $produits]);
    }

    public function aimerProduit(int $produitId, Request $request): JsonResponse
    {
        $useremail = $this->getUserIdFromToken($request);
        if ($useremail->getStatusCode() == 200) {
            $data = json_decode($useremail->getContent(), true);
            $userEmail = $data['email'];

            // Utiliser l'ID utilisateur pour récupérer l'entité User correspondante
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        } else {
            return $useremail;
        }
        $produit = $this->entityManager->getRepository(Produit::class)->find($produitId);

        if (!$produit) {
            return new JsonResponse(['error' => 'Produit non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si l'utilisateur a déjà aimé ce produit
        $existingProduitAime = $this->entityManager->getRepository(ProduitAimes::class)->findOneBy([
            'user' => $user,
            'produit' => $produit,
        ]);

        if ($existingProduitAime) {
            return new JsonResponse(['error' => 'Vous avez déjà aimé ce produit'], Response::HTTP_BAD_REQUEST);
        }

        $produitAime = new ProduitAimes();
        $produitAime->setUser($user);
        $produitAime->setProduit($produit);

        $this->entityManager->persist($produitAime);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Produit aimé ajouté'], Response::HTTP_CREATED);
    }

    public function nePlusAimerProduit(int $produitId, Request $request): JsonResponse
    {
        $useremail = $this->getUserIdFromToken($request);
        if ($useremail->getStatusCode() == 200) {
            $data = json_decode($useremail->getContent(), true);
            $userEmail = $data['email'];

            // Utiliser l'ID utilisateur pour récupérer l'entité User correspondante
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        } else {
            return $useremail;
        }
        $produitAime = $this->entityManager->getRepository(ProduitAimes::class)->findOneBy(['produit' => $produitId, 'user' => $user]);

        if (!$produitAime) {
            return new JsonResponse(['error' => 'Produit non aimé non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($produitAime);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Produit retiré de vos aimés'], Response::HTTP_OK);
    }


    public function getProduitsAimes(Request $request): JsonResponse
    {
        $response = $this->getUserIdFromToken($request);
        if ($response->getStatusCode() !== 200) {
            return $response;  // Retourne l'erreur liée au token
        }

        $data = json_decode($response->getContent(), true);
        $userEmail = $data['email'];
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);

        $produitsAimes = $this->entityManager->getRepository(ProduitAimes::class)->findBy(['user' => $user]);

        $produits = [];
        foreach ($produitsAimes as $produitAime) {
            $produit = $produitAime->getProduit();
            $produits[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'images' => array_map(function ($imagePath) {
                    return $this->getParameter('host') . $imagePath; // Assurez-vous que 'host' est bien configuré dans parameters.yaml
                }, $produit->getImages()),
                // Vous pouvez inclure d'autres propriétés si nécessaire
                'marque' => $produit->getMarque(),
                'code' => $produit->getCode(),
                'ingredients' => array_map(function ($ingredient) {
                    return [
                        'id' => $ingredient->getId(),
                        'nom' => $ingredient->getNom()
                    ];
                }, $produit->getIngredients()->toArray())
            ];
        }

        return new JsonResponse(['produitsAimes' => $produits]);
    }


    public function getUserIdFromToken(Request $request): JsonResponse
    {
        // Extraction du JWT depuis le header 'Authorization'
        $authHeader = $request->headers->get('Authorization');
        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            $this->logger->warning('JWT not provided in headers');
            return new JsonResponse(['error' => 'JWT not provided'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $decodedToken = $this->jwtManager->decode($token);
            if (!$decodedToken) {
                $this->logger->error('Token decoding failed');
                return new JsonResponse(['error' => 'Invalid Token'], Response::HTTP_UNAUTHORIZED);
            }

            $userEmail = $decodedToken['username'] ?? null;
            if (!$userEmail) {
                $this->logger->error('User email not found in token');
                return new JsonResponse(['error' => 'User email not found in token'], Response::HTTP_UNAUTHORIZED);
            }

            $this->logger->info('Token decoded successfully', ['email' => $userEmail]);
            return new JsonResponse(['email' => $userEmail], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Token processing error', ['exception' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }
}
