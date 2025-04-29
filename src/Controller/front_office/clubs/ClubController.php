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
public function contactClub(Request $request, MailerInterface $mailer, int $id, ClubRepository $clubRepo): RedirectResponse
{
    $club = $clubRepo->find($id);
    if (!$club) {
        throw $this->createNotFoundException('Club non trouvé.');
    }

    $nom     = $request->request->get('nom') ?? 'Utilisateur';
    $email   = $request->request->get('email');
    $subject = $request->request->get('subject') ?? 'Message sans sujet';
    $message = $request->request->get('message') ?? '';

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // fallback or redirect with error flash
        $this->addFlash('error', 'Adresse email invalide.');
        return $this->redirectToRoute('app_club_details', [
            'id' => $id,
            '_fragment' => 'contact'
        ]);
    }

    $emailMessage = (new Email())
        ->from($email)
        ->to($club->getClubcontact()) // ✅ send to club's email
        ->subject('[Contact Club] ' . $subject)
        ->text("Message de: $nom <$email>\n\n$message");

    $mailer->send($emailMessage);
    $this->addFlash('success', 'Email was sent.');


    // ✅ use this to trigger the toast on reload
    $request->getSession()->set('show_contact_toast', true);

    return $this->redirectToRoute('app_club_details', [
        'id' => $id,
        '_fragment' => 'contact'
    ]);
}

// test

#[Route('/test-email', name: 'test_email')]
public function testEmail(MailerInterface $mailer): Response
{
    $email = (new Email())
        ->from('malakmzghi@gmail.com')
        ->to('malaak.mzoughiii@gmail.com') // change this to your real email
        ->subject('✅ Test Email from Symfony')
        ->text('This is a test.');

    $mailer->send($email);

    return new Response('✅ Email sent successfully');
}




}
