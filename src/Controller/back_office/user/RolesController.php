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
        // Initialize errors array
        $errors = [];
        $roleName = '';
        
        if ($request->isMethod('POST')) {
            // Validate CSRF token
            if (!$this->isCsrfTokenValid('role_item', $request->request->get('_token'))) {
                return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
            }
            
            // Get role value from form
            $roleName = trim($request->request->get('role'));
            
            // Validate role name
            if (empty($roleName)) {
                $errors['role'] = 'Role name cannot be empty.';
            } elseif (strlen($roleName) < 3) {
                $errors['role'] = 'Role name must be at least 3 characters long.';
            } elseif (strlen($roleName) > 50) {
                $errors['role'] = 'Role name cannot exceed 50 characters.';
            } elseif (!preg_match('/^[A-Za-z0-9_\-\s]+$/', $roleName)) {
                $errors['role'] = 'Role name can only contain alphanumeric characters, spaces, underscores, and hyphens.';
            }
            
            // Check if role already exists
            $existingRole = $entityManager->getRepository(Roles::class)->findOneBy(['role' => $roleName]);
            if ($existingRole) {
                $errors['role'] = 'This role already exists.';
            }
            
            // If no errors, proceed with saving
            if (empty($errors)) {
                // Create a new Roles object and set its role property
                $role = new Roles();
                $role->setRole($roleName);
                
                // Save the new role to the database
                $entityManager->persist($role);
                $entityManager->flush();
                
                // Add flash message and redirect to list roles
                $this->addFlash('success', 'Role added successfully');
                return $this->redirectToRoute('list_roles');
            }
            
            // If there are errors, we'll re-render the form with errors
            $this->addFlash('danger', 'Please correct the errors below.');
        }
        
        // Render the form on GET request or if there are validation errors
        return $this->render('back_office/user/add_role.html.twig', [
            'errors' => $errors,
            'roleName' => $roleName
        ]);
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
        // Initialize errors array
        $errors = [];
        $roleName = $role->getRole();
        
        if ($request->isMethod('POST')) {
            // Validate the CSRF token using a unique token identifier that includes the role ID
            if (!$this->isCsrfTokenValid('update_role' . $role->getId(), $request->request->get('_token'))) {
                return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
            }
            
            // Retrieve the new role value from the form submission
            $roleName = trim($request->request->get('role'));
            
            // Validate role name
            if (empty($roleName)) {
                $errors['role'] = 'Role name cannot be empty.';
            } elseif (strlen($roleName) < 3) {
                $errors['role'] = 'Role name must be at least 3 characters long.';
            } elseif (strlen($roleName) > 50) {
                $errors['role'] = 'Role name cannot exceed 50 characters.';
            } elseif (!preg_match('/^[A-Za-z0-9_\-\s]+$/', $roleName)) {
                $errors['role'] = 'Role name can only contain alphanumeric characters, spaces, underscores, and hyphens.';
            }
            
            // Check if role already exists (only if the name has changed)
            if ($roleName !== $role->getRole()) {
                $existingRole = $entityManager->getRepository(Roles::class)->findOneBy(['role' => $roleName]);
                if ($existingRole) {
                    $errors['role'] = 'This role already exists.';
                }
            }
            
            // If no errors, proceed with updating
            if (empty($errors)) {
                $role->setRole($roleName);
                
                // Persist the changes to the database
                $entityManager->flush();
                
                // Add flash message and redirect to list roles
                $this->addFlash('success', 'Role updated successfully');
                return $this->redirectToRoute('list_roles');
            }
            
            // If there are errors, we'll re-render the form with errors
            $this->addFlash('danger', 'Please correct the errors below.');
        }
        
        // On GET request or if there are validation errors, render the update form
        return $this->render('back_office/user/update_role.html.twig', [
            'role' => $role,
            'errors' => $errors,
            'roleName' => $roleName
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