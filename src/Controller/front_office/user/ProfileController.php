<?php
namespace App\Controller\front_office\user;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use App\Entity\Users;
use App\Entity\Roles;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front_office/user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $usernameError = null;
        $passwordError = null;
        $currentPasswordError = null;
    
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('edit_profile' . $user->getUserId(), $request->request->get('_token'))) {
                $username = $request->request->get('username');
                $email = $request->request->get('email');
                $currentPassword = $request->request->get('current_password');
                $password = $request->request->get('password');
                $confirmPassword = $request->request->get('confirm_password');
                $nom = $request->request->get('nom');
                $prenom = $request->request->get('prenom');
                $avatarFile = $request->files->get('avatar');


                    // Check if current password is empty
        if (empty($currentPassword)) {
            $currentPasswordError = "Current password is required";
            return $this->render('front_office/user/edit_profile.html.twig', [
                'user' => $user,
                'usernameError' => $usernameError,
                'passwordError' => $passwordError,
                'currentPasswordError' => $currentPasswordError,
            ]);
        }


                      // Check if current password is provided
        if (!$currentPassword) {
            $currentPasswordError = "Current password is required";
            return $this->render('front_office/user/edit_profile.html.twig', [
                'user' => $user,
                'usernameError' => $usernameError,
                'passwordError' => $passwordError,
                'currentPasswordError' => $currentPasswordError,
            ]);
        }
    
                // Verify current password first
                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $currentPasswordError = "The current password is incorrect";
                    return $this->render('front_office/user/edit_profile.html.twig', [
                        'user' => $user,
                        'usernameError' => $usernameError,
                        'passwordError' => $passwordError,
                        'currentPasswordError' => $currentPasswordError,
                    ]);
                }
    
                $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
                if ($existingUser && $existingUser->getUserId() !== $user->getUserId()) {
                    $usernameError = "Username already taken";
                    return $this->render('front_office/user/edit_profile.html.twig', [
                        'user' => $user,
                        'usernameError' => $usernameError,
                        'passwordError' => $passwordError,
                        'currentPasswordError' => $currentPasswordError,
                    ]);
                }
    
                if ($password && $confirmPassword) {
                    if ($password !== $confirmPassword) {
                        $passwordError = "Passwords do not match";
                        return $this->render('front_office/user/edit_profile.html.twig', [
                            'user' => $user,
                            'usernameError' => $usernameError,
                            'passwordError' => $passwordError,
                            'currentPasswordError' => $currentPasswordError,
                        ]);
                    }
                    // Hash the new password
                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }
    
                // Rest of your code for avatar and user data updates...
                
                if ($avatarFile) {
                    $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
                    try {
                        $avatarFile->move($this->getParameter('avatars_directory'), $newFilename);
                        $user->setAvatar($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Avatar upload failed');
                        return $this->render('front_office/user/edit_profile.html.twig', [
                            'user' => $user,
                            'usernameError' => $usernameError,
                            'passwordError' => $passwordError,
                            'currentPasswordError' => $currentPasswordError,
                        ]);
                    }
                }
    
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setNom($nom);
                $user->setPrenom($prenom);
    
                $entityManager->flush();
    
                $this->addFlash('success', 'Your profile has been updated successfully');
                return $this->redirectToRoute('app_profile'); // Redirect to profile page
            } else {
                return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
            }
        }
    
        return $this->render('front_office/user/edit_profile.html.twig', [
            'user' => $user,
            'usernameError' => $usernameError,
            'passwordError' => $passwordError,
            'currentPasswordError' => $currentPasswordError,
        ]);
    }
}