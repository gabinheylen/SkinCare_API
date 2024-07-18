<?php
// src/Controller/AuthController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Psr\Log\LoggerInterface;
class AuthController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $jwtManager;
    private $jwtEncodeManager;
    private $logger;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager, JWTEncoderInterface $jwtEncodeManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->jwtEncodeManager = $jwtEncodeManager;

    }

    public function login(Request $request, JWTTokenManagerInterface $jwtManager, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        // Code de gestion de la connexion
        $email = $data['email'];
        $password = $data['password'];

        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Email ou mot de passe incorrect'], Response::HTTP_UNAUTHORIZED);
        }


        $token = $jwtManager->create($user);  // S'assurer que cette méthode accepte un payload personnalisé

        $response = new JsonResponse(['message' => 'Connexion réussie', 'token' => $token]);
        $response->headers->setCookie(Cookie::create(
            'JWT',
            $token,
            time() + 31536000, // 1 an
            '/',
            null,
            true, // Secure, vrai si vous êtes en HTTPS
            true, // HttpOnly
            false,
            'strict' // SameSite
        ));
        
        return $response;
    }

    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Code de gestion de l'inscription
        $user = new User();
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setAge($data['age']);
        $user->setSexe($data['sexe']);
        $user->setPreferences($data['preferences']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        // Ajoutez d'autres attributs selon vos besoins

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Inscription réussie'], Response::HTTP_CREATED);
    }

    public function getUserInfo(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $useremail = $this->getUserIdFromToken($request);
        if ($useremail->getStatusCode() == 200) {
            $responseData = json_decode($useremail->getContent(), true);
            $userEmail = $responseData['email'];
    
            $userRepository = $entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['email' => $userEmail]);
    
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
    
            $userData = [
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'age' => $user->getAge(),
                'sexe' => $user->getSexe(),
                'preferences' => $user->getPreferences(),
                // Ajoutez d'autres attributs selon vos besoins
            ];
    
            return new JsonResponse(['user' => $userData], Response::HTTP_OK);
        }
    
        return $useremail;  // Retourne la réponse d'erreur du token
    }

    public function updateUserInfo(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $useremail = $this->getUserIdFromToken($request);
        if ($useremail->getStatusCode() == 200) {
            $responseData = json_decode($useremail->getContent(), true);
            $userEmail = $responseData['email'];
    
            $userRepository = $entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['email' => $userEmail]);
    
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
    
            $data = json_decode($request->getContent(), true);
    
            if (isset($data['nom'])) {
                $user->setNom($data['nom']);
            }
            if (isset($data['prenom'])) {
                $user->setPrenom($data['prenom']);
            }
            if (isset($data['age'])) {
                $user->setAge($data['age']);
            }
            if (isset($data['sexe'])) {
                $user->setSexe($data['sexe']);
            }
            if (isset($data['preferences'])) {
                $user->setPreferences($data['preferences']);
            }
            if (isset($data['password'])) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            }
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            return new JsonResponse(['message' => 'User updated successfully'], Response::HTTP_OK);
        }
    
        return $useremail;  // Retourne la réponse d'erreur du token
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
            $decodedToken = $this->jwtEncodeManager->decode($token);
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
