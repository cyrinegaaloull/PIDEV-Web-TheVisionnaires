<?php

namespace App\Controller\back_office\clubs;

use App\Entity\Membership;
use App\Entity\ClubMembers;
use App\Repository\MembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MembershipAdminController extends AbstractController
{
    #[Route('/admin/memberships', name: 'admin_membership_list')]
    public function list(
        Request $request,
        MembershipRepository $membershipRepository,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $filter = $request->query->get('filter');
        $status = $request->query->get('status');
        $totalMemberships = $em->getRepository(Membership::class)->count([]);


        $query = $membershipRepository->createQueryBuilder('m')
            ->leftJoin('m.memberid', 'u')
            ->leftJoin('m.clubid', 'c')
            ->addSelect('u', 'c');

        if ($search) {
            $query->andWhere('u.nom LIKE :search OR u.username LIKE :search OR c.clubname LIKE :search')
                  ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $query->andWhere('m.membershipstatus = :status')
                  ->setParameter('status', $status);
        }

        switch ($filter) {
            case 'alpha_asc':
                $query->orderBy('c.clubname', 'ASC');
                break;
            case 'alpha_desc':
                $query->orderBy('c.clubname', 'DESC');
                break;
            case 'date_asc':
                $query->orderBy('m.requestdate', 'ASC');
                break;
            case 'date_desc':
                $query->orderBy('m.requestdate', 'DESC');
                break;
            default:
                $query->orderBy('m.requestdate', 'DESC');
        }

        $memberships = $paginator->paginate(
            $query->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('back_office/clubs/_memberships-table.html.twig', [
                'memberships' => $memberships,
                'total_memberships' => $membershipRepository->count([]),
            ]);
        }

        return $this->render('back_office/clubs/memberships-list.html.twig', [
            'memberships' => $memberships,
            'totalMemberships' => $totalMemberships,
        ]);
    }

    #[Route('/admin/membership/accept/{id}', name: 'admin_membership_validate', methods: ['POST'])]
    public function acceptMembership(int $id, EntityManagerInterface $em): JsonResponse
    {
        $membership = $em->getRepository(Membership::class)->find($id);

        if (!$membership || $membership->getMembershipstatus() !== 'EN_ATTENTE') {
            return new JsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }

        // Update membership status
        $membership->setMembershipstatus('ACCEPTE');

        // Add to club_members table
        $clubMember = new ClubMembers();
        $clubMember->setClubid($membership->getClubid());
        $clubMember->setUserid($membership->getMemberid());
        $clubMember->setJoindate(new \DateTime());

        // Increment count
        $club = $membership->getClubid();
        $club->setMemberscount($club->getMemberscount() + 1);

        // Persist all changed entities
        $em->persist($membership);   // ✅ status
        $em->persist($club);         // ✅ member count
        $em->persist($clubMember);   // ✅ join
        $em->flush();

        return new JsonResponse(['success' => true, 'status' => 'ACCEPTE']);
    }

    

#[Route('/admin/membership/refuse/{id}', name: 'admin_membership_decline', methods: ['POST'])]
public function refuseMembership(int $id, EntityManagerInterface $em): JsonResponse
{
    $membership = $em->getRepository(Membership::class)->find($id);

    if (!$membership || $membership->getMembershipstatus() !== 'EN_ATTENTE') {
        return new JsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }

    // Set membership status to refused
    $membership->setMembershipstatus('REJETE');
    $em->flush();

    return new JsonResponse(['success' => true, 'status' => 'REJETE']);
}

#[Route('/admin/memberships/export', name: 'admin_membership_export')]
public function exportMemberships(MembershipRepository $membershipRepository): Response
{
    $memberships = $membershipRepository->findAll();

    $csvData = "ID,Nom,Prénom,Email,Statut,Date de demande\n";

    foreach ($memberships as $m) {
        $user = $m->getMemberid(); // assuming Memberid is a User
        $csvData .= sprintf(
            "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
            $m->getMembershipid(),
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            $m->getMembershipstatus(),
            $m->getRequestdate()?->format('d/m/Y')
        );
    }

    return new Response($csvData, 200, [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="memberships_export_' . date('Y-m-d') . '.csv"',
    ]);
}


}
