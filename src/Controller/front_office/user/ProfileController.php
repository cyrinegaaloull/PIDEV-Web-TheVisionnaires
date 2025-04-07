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
        // Retrieve the current logged-in user
        $user = $this->getUser();

        // If no user is logged in, redirect to the login page
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front_office/user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
{

    // Retrieve the current logged-in user
    $user = $this->getUser();

    // If no user is logged in, redirect to the login page
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $usernameError = null;
    $passwordError = null;
    
    if ($request->isMethod('POST')) {
        if ($this->isCsrfTokenValid('edit_profile' . $user->getUserId(), $request->request->get('_token'))) {
            // Retrieve form fields from the request
            $username = $request->request->get('username');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            
            // Retrieve the uploaded file (if any)
            $avatarFile = $request->files->get('avatar');
            
            // Check if the username is already used by another user
            $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
            if ($existingUser && $existingUser->getUserId() !== $user->getUserId()) {
                $usernameError = "Username already taken";
                return $this->render('front_office/user/edit_profile.html.twig', [
                    'user' => $user,
                    'usernameError' => $usernameError,
                    'passwordError' => $passwordError,
                ]);
            }
            
            // Check if password fields are filled and match
            if ($password && $confirmPassword) {
                if ($password !== $confirmPassword) {
                    $passwordError = "Passwords do not match";
                    return $this->render('front_office/user/edit_profile.html.twig', [
                        'user' => $user,
                        'usernameError' => $usernameError,
                        'passwordError' => $passwordError,
                    ]);
                }
                
                // Update password if provided
                $user->setPassword($password); // You should hash the password here
            }
            
            // Process the avatar file if a new one is uploaded
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
                    ]);
                }
            }
            
            // Update user information
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            
            // Persist changes
            $entityManager->flush();
            
            $this->addFlash('success', 'Your profile has been updated successfully');
            return $this->redirectToRoute('app_profile');
        } else {
            return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
        }
    }
    
    return $this->render('front_office/user/edit_profile.html.twig', [
        'user' => $user,
        'usernameError' => $usernameError,
        'passwordError' => $passwordError,
    ]);
}
}
