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
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Service\ClubEmailService;


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
public function contactClub(Request $request, ClubRepository $clubRepo, ClubEmailService $emailService, int $id): Response
{
    $club = $clubRepo->find($id);
    if (!$club) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Club introuvable.'], 404);
        }
        $this->addFlash('error', 'Club introuvable.');
        return $this->redirectToRoute('app_clubs');
    }

    // Handle both AJAX and regular form submissions
    if ($request->isXmlHttpRequest()) {
        $data = json_decode($request->getContent(), true);
    } else {
        $data = [
            'name' => $request->request->get('name'),
            'email' => $request->request->get('email'),
            'subject' => $request->request->get('subject'),
            'message' => $request->request->get('message')
        ];
    }

    if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Tous les champs sont obligatoires.'], 400);
        }
        $this->addFlash('error', 'Tous les champs sont obligatoires.');
        return $this->redirectToRoute('app_club_details', ['id' => $id]);
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Email invalide.'], 400);
        }
        $this->addFlash('error', 'Email invalide.');
        return $this->redirectToRoute('app_club_details', ['id' => $id]);
    }

    try {
        $subject = "[Contact Club] Message destiné au club : {$club->getClubname()} ({$club->getClubcontact()})";

        $message = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Message de contact - LocalLens</title>
            </head>
            <body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color: #602080; padding: 20px; border-top-left-radius: 8px; border-top-right-radius: 8px; text-align: center;">
                            <h2 style="color: white; margin: 0;">📩 Nouveau message via le formulaire de contact</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <p style="font-size: 16px; margin-bottom: 20px;">Bonjour, vous avez reçu un nouveau message destiné au club <strong>' . htmlspecialchars($club->getClubname()) . '</strong>.</p>

                            <table width="100%" cellpadding="5" cellspacing="0" style="font-size: 15px; color: #333;">
                                <tr>
                                    <td style="font-weight: bold; width: 150px;">👤 Nom de l\'expéditeur :</td>
                                    <td>' . htmlspecialchars($data['name']) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">📧 Email de l\'expéditeur :</td>
                                    <td>' . htmlspecialchars($data['email']) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">✉️ Message :</td>
                                    <td>' . nl2br(htmlspecialchars($data['message'])) . '</td>
                                </tr>
                            </table>

                            <p style="margin-top: 30px;">Vous pouvez répondre directement à cet email pour contacter l\'expéditeur.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 13px; color: #777; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            © ' . date('Y') . ' LocalLens. Tous droits réservés.
                        </td>
                    </tr>
                </table>
            </body>
            </html>';

        $emailService->send(
            to: 'local.lens14@gmail.com',
            subject: $subject,
            htmlMessage: $message,
            replyTo: $data['email']
        );

        // Set the session flag for toast display
        $request->getSession()->set('show_contact_toast', true);

        // Return different responses based on request type
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        } else {
            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('app_club_details', ['id' => $id]);
        }
    } catch (\Exception $e) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de l\'envoi.'], 500);
        } else {
            $this->addFlash('error', 'Erreur lors de l\'envoi du message.');
            return $this->redirectToRoute('app_club_details', ['id' => $id]);
        }
    }
}




}