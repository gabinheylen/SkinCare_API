<?php

namespace App\Controller;

use App\Entity\ProfilDermatologique;
use App\Repository\ProfilDermatologiqueRepository;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Psr\Log\LoggerInterface;

use App\Entity\User;

class ProfilDermatologiqueController extends AbstractController
{

    private $logger;
    private $jwtManager;
    public function __construct(LoggerInterface $logger, JWTEncoderInterface $jwtManager)
    {
        $this->logger = $logger;
        $this->jwtManager = $jwtManager;
    }
    /**
     * @Route("/profil", name="profil_index", methods={"GET"})
     */
    public function index(ProfilDermatologiqueRepository $repository): JsonResponse
    {
        $profils = $repository->findAll();
        return $this->json($profils);
    }

    /**
     * @Route("/profil/create", name="profil_create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
{
    // Décoder le contenu JSON de la requête
    $data = json_decode($request->getContent(), true);
    
    if ($data === null) {
        return new JsonResponse(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
    }

    // Récupérer l'email de l'utilisateur à partir du token
    $useremail = $this->getUserIdFromToken($request);
    if ($useremail->getStatusCode() == 200) {
        $responseData = json_decode($useremail->getContent(), true);
        $userEmail = $responseData['email'];

        // Utiliser l'email utilisateur pour récupérer l'entité User correspondante
        $user = $em->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Désérialiser les données en objet ProfilDermatologique
        $profil = $serializer->deserialize(json_encode($data), ProfilDermatologique::class, 'json');
        
        if ($profil === null) {
            return new JsonResponse(['error' => 'Désérialisation échouée'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Associer l'utilisateur au profil
        $profil->setUser($user);

        // Valider l'entité ProfilDermatologique
        $errors = $validator->validate($profil);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['message' => implode(', ', $errorMessages)], 400);
        }

        // Persister l'entité
        $em->persist($profil);
        $em->flush();

        // Retourner la réponse JSON avec les groupes de sérialisation appropriés
        return $this->json($profil, 200, [], ['groups' => ['profil_dermatologique']]);
    } else {
        return $useremail; // Retourner la réponse d'erreur de getUserIdFromToken
    }
}

    /**
 * @Route("/profil/update/{id}", name="profil_update", methods={"PUT"})
 */
public function update(int $id, Request $request, EntityManagerInterface $em, ProfilDermatologiqueRepository $repository, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
{
    // Récupérer le profil à mettre à jour
    $profil = $repository->find($id);
    if (!$profil) {
        return $this->json(['message' => 'Profil non trouvé'], 404);
    }

    // Décoder le contenu JSON de la requête
    $data = json_decode($request->getContent(), true);
    if ($data === null) {
        return new JsonResponse(['error' => 'JSON invalide'], JsonResponse::HTTP_BAD_REQUEST);
    }

    // Récupérer l'email de l'utilisateur à partir du token
    $useremail = $this->getUserIdFromToken($request);
    if ($useremail->getStatusCode() == 200) {
        $responseData = json_decode($useremail->getContent(), true);
        $userEmail = $responseData['email'];

        // Utiliser l'email utilisateur pour récupérer l'entité User correspondante
        $user = $em->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Désérialiser les données en objet ProfilDermatologique existant
        $serializer->deserialize(json_encode($data), ProfilDermatologique::class, 'json', ['object_to_populate' => $profil]);

        if ($profil === null) {
            return new JsonResponse(['error' => 'Désérialisation échouée'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Associer l'utilisateur au profil (au cas où cette information peut changer)
        $profil->setUser($user);

        // Valider l'entité ProfilDermatologique
        $errors = $validator->validate($profil);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['message' => implode(', ', $errorMessages)], 400);
        }

        // Persister les modifications
        $em->flush();

        // Retourner la réponse JSON avec les groupes de sérialisation appropriés
        return $this->json($profil, 200, [], ['groups' => ['profil_dermatologique']]);
    } else {
        return $useremail; // Retourner la réponse d'erreur de getUserIdFromToken
    }
}

    /**
     * @Route("/profil/mine", name="profil_mine", methods={"GET"})
     */
    public function getMine(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        // Récupérer l'email de l'utilisateur à partir du token
        $useremail = $this->getUserIdFromToken($request);
        if ($useremail->getStatusCode() == 200) {
            $data = json_decode($useremail->getContent(), true);
            $userEmail = $data['email'];

            // Utiliser l'email utilisateur pour récupérer l'entité User correspondante
            $user = $em->getRepository(User::class)->findOneBy(['email' => $userEmail]);
            if (!$user) {
                return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
            }
            // Récupérer le profil dermatologique de l'utilisateur
            $profil = $em->getRepository(ProfilDermatologique::class)->findOneBy(['User' => $user]);
            if (!$profil) {
                return new JsonResponse(['error' => 'Profil dermatologique non trouvé'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse(json_decode(json_encode($profil->getProfileData())), Response::HTTP_OK);
        } else {
            return $useremail;
        }
    }


    /**
     * @Route("/profil/delete/{id}", name="profil_delete", methods={"DELETE"})
     */
    public function delete(int $id, EntityManagerInterface $em, ProfilDermatologiqueRepository $repository): JsonResponse
    {
        $profil = $repository->find($id);
        if (!$profil) {
            return $this->json(['message' => 'Profil not found'], 404);
        }

        $em->remove($profil);
        $em->flush();

        return $this->json(['message' => 'Profil deleted']);
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