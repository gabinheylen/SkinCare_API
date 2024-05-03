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

    #[Route('/ajouter-mesproduits/{produitId}', name: 'ajouter_mes_produits', methods: ['POST'])]
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

        $mesProduits = new MesProduits();
        $mesProduits->setUser($user);
        $mesProduits->setProduit($produit);

        $this->entityManager->persist($mesProduits);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Produit ajouté à vos produits'], Response::HTTP_CREATED);
    }

    #[Route('/supprimer-mesproduits/{produitId}', name: 'supprimer_mes_produits', methods: ['DELETE'])]
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

    #[Route('/like-produit/{produitId}', name: 'aimer_produit', methods: ['POST'])]
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

        $produitAime = new ProduitAimes();
        $produitAime->setUser($user);
        $produitAime->setProduit($produit);

        $this->entityManager->persist($produitAime);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Produit aimé ajouté'], Response::HTTP_CREATED);
    }

    #[Route('/unlike-produit/{produitId}', name: 'ne_plus_aimer_produit', methods: ['DELETE'])]
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

    public function getUserIdFromToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;

        if (!$token) {
            $this->logger->warning('Token not provided');
            return new JsonResponse(['error' => 'Token not provided'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $decodedToken = $this->jwtManager->decode($token);
            if (!$decodedToken) {
                $this->logger->error('Token decoding failed');
                return new JsonResponse(['error' => 'Invalid Token'], Response::HTTP_UNAUTHORIZED);
            }
            
            $userEmail = $decodedToken['username'] ?? null;
            if (!$userEmail) {
                $this->logger->error('User ID not found in token');
                return new JsonResponse(['error' => 'User ID not found in token'], Response::HTTP_UNAUTHORIZED);
            }

            $this->logger->info('Token decoded successfully', ['email' => $userEmail]);
            return new JsonResponse(['email' => $userEmail], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Token processing error', ['exception' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }


}
