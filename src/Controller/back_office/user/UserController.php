<?php

namespace App\Controller\back_office\user;

use App\Entity\Users;
use App\Entity\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/add-user', name: 'add_user', methods: ['GET', 'POST'])]
    public function addUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usernameError = null;
        $passwordError = null;
        
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('user_item', $request->request->get('_token'))) {
                // Retrieve form fields from the request
                $username = $request->request->get('username');
                $email = $request->request->get('email');
                $password = $request->request->get('password');
                $confirmPassword = $request->request->get('confirm_password');
                $nom = $request->request->get('nom');
                $prenom = $request->request->get('prenom');
                // Retrieve the uploaded file (avatar)
                $avatarFile = $request->files->get('avatar');
                $roleId = $request->request->get('role_id');
                
                // Check if password and confirm password match
                if ($password !== $confirmPassword) {
                    $passwordError = "Passwords do not match";
                    $roles = $entityManager->getRepository(Roles::class)->findAll();
                    return $this->render('back_office/user/add_user.html.twig', [
                        'roles'         => $roles,
                        'passwordError' => $passwordError,
                        'username'      => $username,
                        'email'         => $email,
                        'nom'           => $nom,
                        'prenom'        => $prenom,
                        'role_id'       => $roleId,
                    ]);
                }
                
                // Check if the username already exists
                $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
                if ($existingUser) {
                    $usernameError = "Username already used";
                    $roles = $entityManager->getRepository(Roles::class)->findAll();
                    return $this->render('back_office/user/add_user.html.twig', [
                        'roles'         => $roles,
                        'usernameError' => $usernameError,
                        'username'      => $username,
                        'email'         => $email,
                        'nom'           => $nom,
                        'prenom'        => $prenom,
                        'role_id'       => $roleId,
                    ]);
                }
                
                // Process the avatar file upload; assume avatar is required
                if ($avatarFile) {
                    $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
                    try {
                        $avatarFile->move($this->getParameter('avatars_directory'), $newFilename);
                        $avatarPath = $newFilename; // Store only the filename
                    } catch (FileException $e) {
                        $usernameError = "Avatar upload failed";
                        $roles = $entityManager->getRepository(Roles::class)->findAll();
                        return $this->render('back_office/user/add_user.html.twig', [
                            'roles'         => $roles,
                            'usernameError' => $usernameError,
                            'username'      => $username,
                            'email'         => $email,
                            'nom'           => $nom,
                            'prenom'        => $prenom,
                            'role_id'       => $roleId,
                        ]);
                    }
                } else {
                    // If no file is uploaded, you can set a default avatar
                    $avatarPath = null;
                }
                
                // Retrieve the Roles entity for the given role_id
                $role = $entityManager->getRepository(Roles::class)->find($roleId);
                
                // Create and set up the new user entity
                $user = new Users();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setPassword($password);
                $user->setNom($nom);
                $user->setPrenom($prenom);
                $user->setAvatar($avatarPath);
                $user->setRole($role);
                
                // Persist and flush the new user
                $entityManager->persist($user);
                $entityManager->flush();
                
                // Add flash message and redirect
                $this->addFlash('success', 'User added successfully');
                return $this->redirectToRoute('list_users');
            } else {
                return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
            }
        }
        
        // On GET, fetch all roles and render the add user form
        $roles = $entityManager->getRepository(Roles::class)->findAll();
        return $this->render('back_office/user/add_user.html.twig', [
            'roles'         => $roles,
            'usernameError' => $usernameError,
            'passwordError' => $passwordError,
        ]);
    }
    
    
    
    
    
    

    #[Route('/list-users', name: 'list_users', methods: ['GET'])]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        // Fetch all users from the database
        $users = $entityManager->getRepository(Users::class)->findAll();

        // Render the list in a twig template
        return $this->render('back_office/user/list_users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/update-user/{id}', name: 'update_user', methods: ['GET', 'POST'])]
public function updateUser(Request $request, EntityManagerInterface $entityManager, Users $user): Response
{
    $usernameError = null;
    $passwordError = null;
    
    if ($request->isMethod('POST')) {
        if ($this->isCsrfTokenValid('update_user' . $user->getUserId(), $request->request->get('_token'))) {
            // Retrieve form fields from the request
            $username = $request->request->get('username');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            // Retrieve the uploaded file (if any)
            $avatarFile = $request->files->get('avatar');
            $roleId = $request->request->get('role_id');
            
            // Check if password and confirm password match
            if ($password !== $confirmPassword) {
                $passwordError = "Passwords do not match";
                $roles = $entityManager->getRepository(Roles::class)->findAll();
                return $this->render('back_office/user/update_user.html.twig', [
                    'user'          => $user,
                    'roles'         => $roles,
                    'passwordError' => $passwordError,
                ]);
            }
            
            // Check if the username is already used by another user
            $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
            if ($existingUser && $existingUser->getUserId() !== $user->getUserId()) {
                $usernameError = "Username already used";
                $roles = $entityManager->getRepository(Roles::class)->findAll();
                return $this->render('back_office/user/update_user.html.twig', [
                    'user'          => $user,
                    'roles'         => $roles,
                    'usernameError' => $usernameError,
                ]);
            }
            
            // Process the avatar file if a new one is uploaded
            if ($avatarFile) {
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
                try {
                    $avatarFile->move($this->getParameter('avatars_directory'), $newFilename);
                    $avatarPath = $newFilename; // Store only the filename
                } catch (FileException $e) {
                    // Handle the exception if the file upload fails
                    $usernameError = "Avatar upload failed";
                    $roles = $entityManager->getRepository(Roles::class)->findAll();
                    return $this->render('back_office/user/update_user.html.twig', [
                        'user'          => $user,
                        'roles'         => $roles,
                        'usernameError' => $usernameError,
                    ]);
                }
            } else {
                // If no new file is uploaded, keep the current avatar
                $avatarPath = null;
            }
            
            // Retrieve the Roles entity for the given role_id
            $role = $entityManager->getRepository(Roles::class)->find($roleId);
            
            // Update the user's properties
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setAvatar($avatarPath);
            $user->setRole($role);
            
            // Persist the changes
            $entityManager->flush();
            
            // Add flash message and redirect to list
            $this->addFlash('success', 'User updated successfully');
            return $this->redirectToRoute('list_users');
        } else {
            return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
        }
    }
    
    // On GET, fetch roles and render the update form
    $roles = $entityManager->getRepository(Roles::class)->findAll();
    return $this->render('back_office/user/update_user.html.twig', [
        'user'  => $user,
        'roles' => $roles,
    ]);
}

    
    
    

#[Route('/delete-user/{id}', name: 'delete_user', methods: ['POST'])]
public function deleteUser(Request $request, EntityManagerInterface $entityManager, Users $user): Response
{
    if ($this->isCsrfTokenValid('delete_user' . $user->getUserId(), $request->request->get('_token'))) {
        $entityManager->remove($user);
        $entityManager->flush();
        // Use flash type "danger" for delete messages
        $this->addFlash('danger', 'User deleted successfully');
    } else {
        $this->addFlash('error', 'Invalid CSRF token');
    }
    
    return $this->redirectToRoute('list_users');
}

}
