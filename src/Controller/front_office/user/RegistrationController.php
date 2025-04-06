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
use Symfony\Component\HttpFoundation\File\UploadedFile; // Make sure this is imported

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
            // It's better practice to render the form on GET or redirect to a dedicated registration page
            // Redirecting to login might be confusing if the user intended to register.
            // Consider having a separate route/template for GET requests to '/register'
            // For now, keeping original logic:
             return $this->redirectToRoute('app_login'); // Or perhaps render a registration form template directly
        }

        // Get form data
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        /** @var UploadedFile|null $avatarFile */
        $avatarFile = $request->files->get('avatar'); // Get the file object

        // Store form data for repopulation
        $formData = [
            'username' => $username,
            'email' => $email,
            'nom' => $nom,
            'prenom' => $prenom
            // Do not store password or avatar file info here for security/simplicity
        ];

        $errors = [];

        try {
            // Basic validation (consider using Symfony Forms & Validator for robust validation)
            if (empty($username) || empty($email) || empty($password) || empty($nom) || empty($prenom)) {
                $errors[] = 'All fields except avatar are required';
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

            // Validate avatar if provided (optional: add checks for size, mime type etc.)
            if ($avatarFile && !$avatarFile->isValid()) {
                 $errors[] = 'Avatar upload failed: ' . $avatarFile->getErrorMessage();
            }


            // If there are any errors, render the login template but stay on signup
            if (!empty($errors)) {
                return $this->render('front_office/user/login.html.twig', [
                    'last_username' => '', // Or maybe $email if you use email for login
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

            // Using plaintext as per your security.yaml - VERY INSECURE!
            // You should absolutely use a password hasher.
            // Example with hashing (assuming you configured a hasher):
            // $hashedPassword = $passwordHasher->hashPassword($user, $password);
            // $user->setPassword($hashedPassword);
            $user->setPassword($password); // Keep original logic as requested, but strongly advise against it.

            // Set default role as USER
            $userRole = $entityManager->getRepository(Roles::class)->findOneBy(['role' => 'USER']);
            if (!$userRole) {
                // Create the USER role if it doesn't exist
                $userRole = new Roles();
                $userRole->setRole('USER');
                $entityManager->persist($userRole);
                // No need to flush here, will be flushed with the user
            }
            $user->setRole($userRole);

            // --- MODIFIED AVATAR LOGIC ---
            $avatarFilename = null; // Initialize avatar filename as null

            if ($avatarFile) { // Check if a file was actually uploaded
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'), // Your configured upload directory
                        $newFilename
                    );
                    // On success, set the filename (without path)
                    $avatarFilename = $newFilename;

                } catch (FileException $e) {
                    // Log the error maybe
                    // error_log('Avatar upload failed: ' . $e->getMessage());
                    // Add a flash message or error if you want to inform the user specifically about avatar failure
                    $this->addFlash('warning', 'Account created, but avatar could not be uploaded.');
                    // Keep $avatarFilename as null (database default)
                }
            }
             // If $avatarFile was null initially, or if move failed, $avatarFilename remains null.
            $user->setAvatar($avatarFilename); // Set the filename or null
            // --- END OF MODIFIED AVATAR LOGIC ---


            // Save user to database
            $entityManager->persist($user);
            $entityManager->flush();

            // Add success flash message
            $this->addFlash('success', 'Account created successfully! You can now log in.');

            // Redirect with success parameter
            return $this->redirectToRoute('app_login', ['registration' => 'success']);

        } catch (\Exception $e) {
            // Log the general error
            // error_log('Registration failed: ' . $e->getMessage());
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