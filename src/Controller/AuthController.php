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

class AuthController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $jwtManager;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
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

    public function logout(): void
    {
        // Code de gestion de la déconnexion
        // Ici, Symfony gère automatiquement la déconnexion lors de la soumission de la requête de déconnexion
        // Vous n'avez pas besoin de faire quoi que ce soit dans cette fonction
    }


}
