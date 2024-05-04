<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ProfilDermatologique;
use Doctrine\ORM\EntityManagerInterface;

class ProfilDermatologiqueController 
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $profilDermatologique = new ProfilDermatologique();
        $profilDermatologique->setTypeDePeau($data['type_de_peau'] ?? []);
        $profilDermatologique->setSensibilite($data['sensibilite'] ?? []);
        $profilDermatologique->setAutre($data['autre'] ?? []);

        $this->entityManager->persist($profilDermatologique);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Profil dermatologique créé avec succès'], JsonResponse::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $profilDermatologique = $this->entityManager->getRepository(ProfilDermatologique::class)->find($id);

        if (!$profilDermatologique) {
            return new JsonResponse(['message' => 'Profil dermatologique non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $profilDermatologique->setTypeDePeau($data['type_de_peau'] ?? $profilDermatologique->getTypeDePeau());
        $profilDermatologique->setSensibilite($data['sensibilite'] ?? $profilDermatologique->getSensibilite());
        $profilDermatologique->setAutre($data['autre'] ?? $profilDermatologique->getAutre());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Profil dermatologique mis à jour avec succès'], JsonResponse::HTTP_OK);
    }

    public function getAll(): JsonResponse
    {
        $profilsDermatologiques = $this->entityManager->getRepository(ProfilDermatologique::class)->findAll();
        $profilsDermatologiquesArray = [];

        foreach ($profilsDermatologiques as $profilDermatologique) {
            $profilsDermatologiquesArray[] = [
                'id' => $profilDermatologique->getId(),
                'type_de_peau' => $profilDermatologique->getTypeDePeau(),
                'sensibilite' => $profilDermatologique->getSensibilite(),
                'autre' => $profilDermatologique->getAutre(),
            ];
        }

        return new JsonResponse($profilsDermatologiquesArray, JsonResponse::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $profilDermatologique = $this->entityManager->getRepository(ProfilDermatologique::class)->find($id);

        if (!$profilDermatologique) {
            return new JsonResponse(['message' => 'Profil dermatologique non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($profilDermatologique);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Profil dermatologique supprimé avec succès'], JsonResponse::HTTP_OK);
    }
}
