<?php

namespace App\Controller\back_office\user;

use App\Entity\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RolesController extends AbstractController
{
    #[Route('/add-role', name: 'add_role', methods: ['GET', 'POST'])]
    public function addRole(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            // Validate CSRF token
            if ($this->isCsrfTokenValid('role_item', $request->request->get('_token'))) {
                // Get role value from form
                $roleName = $request->request->get('role');
                
                // Create a new Roles object and set its role property
                $role = new Roles();
                $role->setRole($roleName);
                
                // Save the new role to the database
                $entityManager->persist($role);
                $entityManager->flush();
                
                // Add flash message and redirect to list roles
                $this->addFlash('success', 'Role added successfully');
                return $this->redirectToRoute('list_roles');
            } else {
                return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
            }
        }
        
        // Render the form on GET request
        return $this->render('back_office/user/add_role.html.twig');
    }


    #[Route('/list-roles', name: 'list_roles', methods: ['GET'])]
    public function listRoles(EntityManagerInterface $entityManager): Response
    {
        // Fetch all roles from the database
        $roles = $entityManager->getRepository(Roles::class)->findAll();

        // Render the list in a twig template
        return $this->render('back_office/user/list_roles.html.twig', [
            'roles' => $roles
        ]);
    }

    #[Route('/update-role/{id}', name: 'update_role', methods: ['GET', 'POST'])]
    public function updateRole(Request $request, EntityManagerInterface $entityManager, Roles $role): Response
    {
        if ($request->isMethod('POST')) {
            // Validate the CSRF token using a unique token identifier that includes the role ID
            if ($this->isCsrfTokenValid('update_role' . $role->getId(), $request->request->get('_token'))) {
                // Retrieve the new role value from the form submission
                $roleName = $request->request->get('role');
                $role->setRole($roleName);
                
                // Persist the changes to the database
                $entityManager->flush();
                
                // Add flash message and redirect to list roles
                $this->addFlash('success', 'Role updated successfully');
                return $this->redirectToRoute('list_roles');
            } else {
                return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
            }
        }
        
        // On GET request, render the update form with the current role data
        return $this->render('back_office/user/update_role.html.twig', [
            'role' => $role,
        ]);
    }


    #[Route('/delete-role/{id}', name: 'delete_role', methods: ['POST'])]
    public function deleteRole(Request $request, EntityManagerInterface $entityManager, Roles $role): Response
    {
        if ($this->isCsrfTokenValid('delete_role' . $role->getId(), $request->request->get('_token'))) {
            $entityManager->remove($role);
            $entityManager->flush();
            // Use "danger" flash type so it appears in red
            $this->addFlash('danger', 'Role deleted successfully');
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }
        
        return $this->redirectToRoute('list_roles');
    }
    
}
