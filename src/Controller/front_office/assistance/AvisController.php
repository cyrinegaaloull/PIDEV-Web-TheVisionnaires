<?php

namespace App\Controller\front_office\assistance;

use App\Entity\Avis;
use App\Entity\Etablissement;
use App\Entity\Users;
use App\Repository\AvisRepository;
use App\Repository\EtablissementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class AvisController extends AbstractController
{
    #[Route('/api/etablissements/{id}/avis', name: 'api_ajouter_avis', methods: ['POST', 'GET'])]
    public function ajouterAvisAjax(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        EtablissementRepository $etablissementRepository
    ): JsonResponse {
        try {
            // Récupérer l'établissement
            $etablissement = $etablissementRepository->find($id);
            if (!$etablissement) {
                return new JsonResponse(['success' => false, 'message' => 'Établissement introuvable'], 404);
            }

            // Vérifier si l'utilisateur est connecté
            $user = $this->getUser();
            if (!$user instanceof Users) {
                return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour laisser un avis'], 401);
            }

            // Récupérer les données envoyées
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['success' => false, 'message' => 'Invalid JSON payload'], 400);
            }

            $rating = $data['rating'] ?? null;

            // Vérifier que la note est valide
            if ($rating === null || !is_numeric($rating) || $rating < 1 || $rating > 5) {
                return new JsonResponse(['success' => false, 'message' => 'La note doit être comprise entre 1 et 5'], 400);
            }

            // Vérifier si l'utilisateur a déjà laissé un avis pour cet établissement
            $existingAvis = $entityManager->getRepository(Avis::class)->findOneBy([
                'user' => $user,
                'etablissement' => $etablissement
            ]);

            if ($existingAvis) {
                // Mettre à jour l'avis existant
                $existingAvis->setRating($rating);
                $existingAvis->setDateavis(new \DateTime());
                $message = 'Votre avis a été mis à jour';
            } else {
                // Créer un nouvel avis
                $avis = new Avis();
                $avis->setUser($user);
                $avis->setEtablissement($etablissement);
                $avis->setRating($rating);
                $avis->setDateavis(new \DateTime());

                $entityManager->persist($avis);
                $message = 'Votre avis a été ajouté';
            }

            try {
                $entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                return new JsonResponse(['success' => false, 'message' => 'Vous avez déjà soumis un avis pour cet établissement'], 400);
            }

            // Calculer la moyenne directement avec DQL
            $qb = $entityManager->createQueryBuilder();
            $qb->select('AVG(a.rating) as average')
                ->from(Avis::class, 'a')
                ->where('a.etablissement = :etabId')
                ->setParameter('etabId', $etablissement);
            $avg = $qb->getQuery()->getSingleScalarResult();

            // Compter le nombre d'avis
            $qb2 = $entityManager->createQueryBuilder();
            $qb2->select('COUNT(a.avisid) as count')
                ->from(Avis::class, 'a')
                ->where('a.etablissement = :etabId')
                ->setParameter('etabId', $etablissement);
            $count = $qb2->getQuery()->getSingleScalarResult();

            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'average' => round($avg ?: 0, 1),
                'count' => (int)($count ?: 0)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    #[Route('/etablissements/{id}/avis/user', name: 'get_user_avis', methods: ['GET'])]
    public function getUserAvis(
        int $id,
        EtablissementRepository $etablissementRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $etablissement = $etablissementRepository->find($id);
        if (!$etablissement) {
            return new JsonResponse(['rating' => 0]);
        }

        $user = $this->getUser();
        if (!$user instanceof Users) {
            return new JsonResponse(['rating' => 0]);
        }

        $avis = $entityManager->getRepository(Avis::class)->findOneBy([
            'user' => $user,
            'etablissement' => $etablissement
        ]);

        return new JsonResponse(['rating' => $avis ? $avis->getRating() : 0]);
    }

    #[Route('/api/etablissements/{id}/rating', name: 'api_etablissement_rating', methods: ['GET'])]
    public function getEtablissementRating(
        int $id,
        EtablissementRepository $etablissementRepository,
        AvisRepository $avisRepository
    ): JsonResponse {
        $etablissement = $etablissementRepository->find($id);
        if (!$etablissement) {
            return new JsonResponse(['error' => 'Établissement introuvable'], 404);
        }

        $average = $avisRepository->getAverageRatingForEtablissement($etablissement->getEtabid());
        $count = $avisRepository->countAvisForEtablissement($etablissement->getEtabid());

        return new JsonResponse([
            'average' => round($average, 1),
            'count' => $count
        ]);
    }
}
