<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\NoteProduit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NoteProduitController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $noteProduit = new NoteProduit();
        $noteProduit->setNote($data['note']);

        $utilisateur = $this->entityManager->getRepository(User::class)->find($data['utilisateur_id']);
        if (!$utilisateur) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $noteProduit->setUser($utilisateur);

        $this->entityManager->persist($noteProduit);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'NoteProduit créée avec succès'], JsonResponse::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $noteProduit = $this->entityManager->getRepository(NoteProduit::class)->find($id);

        if (!$noteProduit) {
            return new JsonResponse(['message' => 'NoteProduit non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }

        $noteProduit->setNote($data['note'] ?? $noteProduit->getNote());

        $utilisateur = $this->entityManager->getRepository(User::class)->find($data['utilisateur_id']);
        if (!$utilisateur) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $noteProduit->setUtilisateur($utilisateur);

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'NoteProduit mise à jour avec succès'], JsonResponse::HTTP_OK);
    }

    public function getAll(): JsonResponse
    {
        $notesProduits = $this->entityManager->getRepository(NoteProduit::class)->findAll();
        $notesProduitsArray = [];

        foreach ($notesProduits as $noteProduit) {
            $notesProduitsArray[] = [
                'id' => $noteProduit->getId(),
                'note' => $noteProduit->getNote(),
                'utilisateur_id' => $noteProduit->getUtilisateur()->getId()
            ];
        }

        return new JsonResponse($notesProduitsArray, JsonResponse::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $noteProduit = $this->entityManager->getRepository(NoteProduit::class)->find($id);

        if (!$noteProduit) {
            return new JsonResponse(['message' => 'NoteProduit non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($noteProduit);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'NoteProduit supprimée avec succès'], JsonResponse::HTTP_OK);
    }
}
