<?php
// src/Controller/EvaluationController.php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\ProfilDermatologique;
use App\Entity\User;
use App\Repository\ProduitRepository;
use App\Repository\ProfilDermatologiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Psr\Log\LoggerInterface;

class EvaluationController extends AbstractController
{
    private $entityManager;
    private $logger;
    private $jwtManager;
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, JWTEncoderInterface $jwtManager)
    {
        $this->logger = $logger;
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
    }

    #[Route('/evaluate/{productId}', name: 'product_evaluate', methods: ['GET'])]
    public function evaluateProduct(
        Request $request,
        int $productId,
        ProduitRepository $produitRepository,
        ProfilDermatologiqueRepository $profilDermatologiqueRepository
    ): JsonResponse {
        // Récupérer le produit par son ID
        $produit = $produitRepository->find($productId);
        if (!$produit) {
            return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Récupérer l'utilisateur connecté
        // Récupérer l'email de l'utilisateur à partir du token
        $useremail = $this->getUserIdFromToken($request);
        if ($useremail->getStatusCode() == 200) {
            $responseData = json_decode($useremail->getContent(), true);
            $userEmail = $responseData['email'];

            // Utiliser l'email utilisateur pour récupérer l'entité User correspondante
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
            if (!$user) {
                return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }


            // Récupérer le profil dermatologique de l'utilisateur
            $profilDermatologique = $profilDermatologiqueRepository->findOneBy(['User' => $user]);
            if (!$profilDermatologique) {
                return new JsonResponse(['error' => 'Dermatological profile not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            // Récupérer les détails des deux JSON
            $productDetails = $produit->getDetails();
            $profileData = $profilDermatologique->getProfileData();

            // Comparer les JSON pour déterminer la compatibilité
            $compatibility = $this->evaluateCompatibility($productDetails, $profileData);

            // Calculer la note globale
            $overallScore = $this->calculateOverallScore($compatibility);

            // Ajouter la note globale à la réponse
            $compatibility['overallScore'] = $overallScore;

            return new JsonResponse($compatibility);
        } else {
            return $useremail;
        }
    }

    private function calculateOverallScore(array $compatibility): int
    {
        $totalCriteria = count($compatibility);
        $compatibleCriteria = 0;

        foreach ($compatibility as $key => $value) {
            if ($value['compatible']) {
                $compatibleCriteria++;
            }
        }

        // Calculer la note sur 100
        $score = ($compatibleCriteria / $totalCriteria) * 100;

        return (int) $score;
    }

    private function evaluateCompatibility(array $productDetails, array $profileData): array
    {
        $allowedValues = [
            'typeOfSkin' => ['Oily', 'Dry', 'Combination', 'Normal'],
            'skinSensitivity' => ['High', 'Medium', 'Low'],
            'commonSkinProblems' => ['Acne', 'Eczema', 'Psoriasis', 'None'],
            'skinTone' => ['Light', 'Medium', 'Dark'],
            'skinUndertone' => ['Cool', 'Warm', 'Neutral'],
            'environmentalConditions' => ['Urban', 'Rural', 'Suburban'],
            'skincareHabits' => ['Regular', 'Irregular', 'None'],
            'allergiesIntolerances' => ['None', 'Fragrance', 'Preservatives', 'Dyes'],
            'lifestyleFactors' => ['High stress', 'Low stress', 'Moderate stress'],
            'medicalHistory' => ['Eczema', 'Psoriasis', 'None']
        ];

        $compatibility = [];
        foreach ($allowedValues as $key => $values) {
            $productValue = $productDetails[$key] ?? null;
            $profileValue = $profileData[$key] ?? null;

            if ($productValue && $profileValue) {
                $compatibility[$key] = [
                    'productValue' => $productValue,
                    'profileValue' => $profileValue,
                    'compatible' => $this->isCompatible($key, $productValue, $profileValue)
                ];
            }
        }

        return $compatibility;
    }

    private function isCompatible(string $key, $productValue, $profileValue): bool
    {
        // Logique de compatibilité
        // Vous pouvez personnaliser cette logique en fonction des besoins

        if ($key == 'typeOfSkin' || $key == 'skinSensitivity' || $key == 'commonSkinProblems' || $key == 'skinTone' || $key == 'skinUndertone') {
            return $productValue === $profileValue;
        }

        if ($key == 'environmentalConditions' || $key == 'skincareHabits' || $key == 'allergiesIntolerances' || $key == 'lifestyleFactors' || $key == 'medicalHistory') {
            // Ajouter des règles de compatibilité spécifiques
            return $productValue === $profileValue;
        }

        return true; // Par défaut, considérons les valeurs compatibles
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
