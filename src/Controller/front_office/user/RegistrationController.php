<?php

namespace App\Controller\front_office\user;

use App\Entity\Users;
use App\Entity\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        // If the user is already logged in, redirect to home
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        
        if (!$request->isMethod('POST')) {
            return $this->redirectToRoute('app_login');
        }
        
        // Get form data
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        
        // Store form data for repopulation
        $formData = [
            'username' => $username,
            'email' => $email,
            'nom' => $nom,
            'prenom' => $prenom
        ];
        
        $errors = [];
        
        try {
            // Basic validation
            if (empty($username) || empty($email) || empty($password) || empty($nom) || empty($prenom)) {
                $errors[] = 'All fields are required';
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            // Check if username already exists
            $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
            if ($existingUser) {
                $errors[] = 'Username already exists';
            }
            
            // Check if email already exists
            $existingEmail = $entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
            if ($existingEmail) {
                $errors[] = 'Email already exists';
            }
            
            // If there are any errors, render the login template but stay on signup
            if (!empty($errors)) {
                return $this->render('front_office/user/login.html.twig', [
                    'last_username' => '',
                    'error' => null,
                    'signup_errors' => $errors,
                    'form_data' => $formData,
                    'show_signup' => true
                ]);
            }
            
            // Create a new user
            $user = new Users();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            
            // Using plaintext as per your security.yaml
            $user->setPassword($password);
            
            // Set default role as USER
            $userRole = $entityManager->getRepository(Roles::class)->findOneBy(['role' => 'USER']);
            if (!$userRole) {
                // Create the USER role if it doesn't exist
                $userRole = new Roles();
                $userRole->setRole('USER');
                $entityManager->persist($userRole);
                $entityManager->flush();
            }
            $user->setRole($userRole);
            
            // Handle avatar upload
            $avatarFile = $request->files->get('avatar');
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();
                
                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                    $user->setAvatar($newFilename);
                } catch (FileException $e) {
                    // Use default avatar on error
                    $user->setAvatar('default-avatar.png');
                }
            } else {
                // Set default avatar
                $user->setAvatar('default-avatar.png');
            }
            
            // Save user to database
            $entityManager->persist($user);
            $entityManager->flush();
            
            // Add success flash message
            $this->addFlash('success', 'Account created successfully! You can now log in.');
            
            // Redirect with success parameter
            return $this->redirectToRoute('app_login', ['registration' => 'success']);
            
        } catch (\Exception $e) {
            $errors[] = 'An error occurred during registration. Please try again.';
            
            return $this->render('front_office/user/login.html.twig', [
                'last_username' => '',
                'error' => null,
                'signup_errors' => $errors,
                'form_data' => $formData,
                'show_signup' => true
            ]);
        }
    }
}