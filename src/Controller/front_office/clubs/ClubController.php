<?php

namespace App\Controller\front_office\clubs;

use App\Repository\ClubRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Activite;
use App\Entity\Club;
use App\Entity\Membership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Form\ContactFormType;
use Symfony\Component\Mime\Email as SymfonyEmail;


class ClubController extends AbstractController
{
    #[Route('/clubs', name: 'app_clubs')]
    public function index(Request $request, ClubRepository $clubRepository, PaginatorInterface $paginator): Response
    {

        $user = $this->getUser();

        $search = $request->query->get('search');
        $category = $request->query->get('category');

        $queryBuilder = $clubRepository->createQueryBuilder('c');

        if ($search) {
            $queryBuilder->andWhere('c.clubname LIKE :search OR c.clublocation LIKE :search')
                        ->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $queryBuilder->andWhere('c.clubcategory = :category')
                        ->setParameter('category', $category);
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9
        );

        $categoriesRaw = $clubRepository->createQueryBuilder('c')
        ->select('DISTINCT c.clubcategory')
        ->where('c.clubcategory IS NOT NULL')
        ->getQuery()
        ->getScalarResult();

        $categories = array_map(fn($row) => $row['clubcategory'], $categoriesRaw);
        sort($categories); 



        return $this->render('front_office/clubs/clubs.html.twig', [
            'clubs' => $pagination,
            'user' => $user,
            'categories' => $categories,
        ]);
    }

#[Route('/clubs/{id}', name: 'app_club_details')]
public function show(
    int $id,
    ClubRepository $clubRepository,
    EntityManagerInterface $em
): Response {

    $user = $this->getUser();

    // Fetch the club
    $club = $clubRepository->find($id);

    if (!$club) {
        throw $this->createNotFoundException('Ce club est introuvable.');
    }

    // Count all activities (regardless of status)
    $activitiesCount = $em->getRepository(Activite::class)
        ->createQueryBuilder('a')
        ->select('COUNT(a.activiteid)')
        ->where('a.clubid = :club')
        ->setParameter('club', $club)
        ->getQuery()
        ->getSingleScalarResult();

    // Get only A_venir activities
    $upcomingActivities = $em->getRepository(Activite::class)
        ->createQueryBuilder('a')
        ->where('a.clubid = :club')
        ->andWhere('a.activitestatus = :status')
        ->setParameter('club', $club)
        ->setParameter('status', 'A_venir')
        ->orderBy('a.activitedate', 'ASC')
        ->getQuery()
        ->getResult();

    // Related clubs from same category
    $relatedClubs = $clubRepository->createQueryBuilder('c')
        ->where('c.clubcategory = :cat')
        ->andWhere('c.clubid != :id')
        ->setParameter('cat', $club->getClubcategory())
        ->setParameter('id', $club->getClubid())
        ->setMaxResults(3)
        ->getQuery()
        ->getResult();

    return $this->render('front_office/clubs/club-details.html.twig', [
        'club' => $club,
        'user' => $user,
        'upcomingActivities' => $upcomingActivities,
        'activitiesCount' => $activitiesCount,
        'relatedClubs' => $relatedClubs,
    ]);
}


#[Route('/join-club/{id}', name: 'join_club', methods: ['POST'])]
public function joinClubAjax(int $id, Request $request, EntityManagerInterface $em, ClubRepository $clubRepo): JsonResponse
{
    $user = $this->getUser();
    if (!$user) {
        return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté.'], 401);
    }

    $club = $clubRepo->find($id);
    if (!$club) {
        return new JsonResponse(['success' => false, 'message' => 'Club non trouvé.'], 404);
    }

    // Check if already requested
    $existing = $em->getRepository(Membership::class)->findOneBy([
        'clubid' => $club,
        'memberid' => $user
    ]);

    if ($existing) {
        return new JsonResponse(['success' => false, 'message' => 'Demande déjà envoyée.'], 400);
    }

    $membership = new Membership();
    $membership->setClubid($club);
    $membership->setMemberid($user);
    $membership->setRequestdate(new \DateTime());
    $membership->setMembershipstatus('EN_ATTENTE');

    $em->persist($membership);
    $em->flush();

    return new JsonResponse(['success' => true]);
}

#[Route('/clubs/contact/{id}', name: 'club_contact', methods: ['POST'])]
public function contactClub(Request $request, MailerInterface $mailer, ClubRepository $clubRepo, int $id): JsonResponse
{
    $club = $clubRepo->find($id);
    
    if (!$club) {
        return new JsonResponse(['success' => false, 'message' => 'Club introuvable.'], 404);
    }

    // Get data directly from the request for AJAX submission
    $data = json_decode($request->getContent(), true);
    
    // Manual validation since we're not using the form component for AJAX
    if (empty($data['votre nom']) || empty($data['votre email']) || empty($data['votre message'])) {
        return new JsonResponse(['success' => false, 'message' => 'Tous les champs sont obligatoires.'], 400);
    }
    
    if (!filter_var($data['votre email'], FILTER_VALIDATE_EMAIL)) {
        return new JsonResponse(['success' => false, 'message' => 'Email invalide.'], 400);
    }

    try {
        $email = (new SymfonyEmail())
            ->from($data['votre email'])
            ->to($club->getClubcontact())
            ->subject('[Contact Club] ' . ($data['sujet'] ?? $club->getClubname()))
            ->text(
                "Nom: {$data['votre nom']}\n" .
                "Email: {$data['votre email']}\n\n" .
                "Message:\n{$data['votre message']}"
            );

        $mailer->send($email);
        
        // Set the session variable for the toast
        $request->getSession()->set('show_contact_toast', true);
        
        return new JsonResponse(['success' => true]);
    } catch (\Exception $e) {
        // Log the error for debugging
        error_log('Error sending email: ' . $e->getMessage());
        return new JsonResponse(['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'email.'], 500);
    }
}

}