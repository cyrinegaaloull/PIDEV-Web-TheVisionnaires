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
        SluggerInterface $slugger = null // Make optional since we'll add error handling
    ): Response {
        // If the user is already logged in, redirect to home
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        
        // Debug request method
        if (!$request->isMethod('POST')) {
            return $this->redirectToRoute('app_login');
        }
        
        try {
            // Get form data
            $username = $request->request->get('username');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            
            // Debug received data (temporarily log or dump)
            // dump($username, $email, $password, $confirmPassword, $nom, $prenom);
            
            // Basic validation
            if (empty($username) || empty($email) || empty($password) || empty($nom) || empty($prenom)) {
                $this->addFlash('signup_error', 'All fields are required');
                return $this->redirectToRoute('app_login');
            }
            
            if ($password !== $confirmPassword) {
                $this->addFlash('signup_error', 'Passwords do not match');
                return $this->redirectToRoute('app_login');
            }
            
            // Check if username already exists
            $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
            if ($existingUser) {
                $this->addFlash('signup_error', 'Username already exists');
                return $this->redirectToRoute('app_login');
            }
            
            // Check if email already exists
            $existingEmail = $entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
            if ($existingEmail) {
                $this->addFlash('signup_error', 'Email already exists');
                return $this->redirectToRoute('app_login');
            }
            
            // Create a new user
            $user = new Users();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            
            // Store password
            $user->setPassword($password);
            
            // Set default role as USER
            $userRole = $entityManager->getRepository(Roles::class)->findOneBy(['role' => 'USER']);
            if (!$userRole) {
                // Create the USER role if it doesn't exist
                $userRole = new Roles();
                $userRole->setRole('USER');
                $entityManager->persist($userRole);
                $entityManager->flush(); // Flush now to get the ID
            }
            $user->setRole($userRole);
            
            // Handle avatar upload
            $avatarFile = $request->files->get('avatar');
            if ($avatarFile && $slugger) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();
                
                try {
                    // Check if avatars_directory parameter exists
                    if (!$this->getParameter('avatars_directory')) {
                        throw new \Exception('avatars_directory parameter is not defined');
                    }
                    
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                    $user->setAvatar($newFilename);
                } catch (FileException | \Exception $e) {
                    // Use a default avatar if there's an error with file upload
                    $user->setAvatar('default-avatar.png');
                    // Log error but proceed with registration
                    $this->addFlash('warning', 'Avatar could not be uploaded. Using default avatar.');
                }
            } else {
                // Set default avatar
                $user->setAvatar('default-avatar.png');
            }
            
            // Save user to database
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Account created successfully! You can now log in.');
            
        } catch (\Exception $e) {
            // Log the exception
            // $this->get('logger')->error('Registration error: ' . $e->getMessage());
            
            // Add a flash message with the error
            $this->addFlash('signup_error', 'An error occurred during registration: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('app_login');
    }
}