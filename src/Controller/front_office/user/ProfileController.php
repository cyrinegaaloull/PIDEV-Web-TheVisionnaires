<?php

namespace App\Controller\front_office\user;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger // Inject Slugger for safe file names
    ): Response {
        /** @var Users $user */ // Type hint for autocompletion
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $errors = []; // Initialize an array to hold all validation errors

        if ($request->isMethod('POST')) {
            // 1. CSRF Token Validation
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('edit_profile' . $user->getUserId(), $submittedToken)) {
                // It's generally better to throw an exception or return a generic error response
                // for security reasons, rather than re-rendering the form with a specific error.
                // $this->addFlash('error', 'Invalid security token.');
                // return $this->redirectToRoute('app_profile_edit');
                return new Response('Invalid CSRF token.', Response::HTTP_FORBIDDEN);
            }

            // 2. Retrieve Input Data
            $username = trim((string)$request->request->get('username'));
            $email = trim((string)$request->request->get('email'));
            $nom = trim((string)$request->request->get('nom'));
            $prenom = trim((string)$request->request->get('prenom'));
            $currentPassword = (string)$request->request->get('current_password'); // Don't trim passwords
            $newPassword = (string)$request->request->get('password');
            $confirmPassword = (string)$request->request->get('confirm_password');
            $avatarFile = $request->files->get('avatar');

            // 3. Perform Custom Validations

            // --- Basic Info Validation ---
            if (empty($username)) {
                $errors['username'] = 'Username is required.';
            } else {
                // Trim whitespace
                $username = trim($username);
                if (strlen($username) < 3) {
                    $errors['username'] = 'Username must be at least 3 characters long.';
                } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                    $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
                } elseif (strlen($username) > 50) {
                    $errors['username'] = 'Username cannot exceed 50 characters.';
                } elseif ($username !== $user->getUsername()) {
                    $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
                    if ($existingUser) {
                        $errors['username'] = 'Username already taken.';
                    }
                }
            }

            if (empty($email)) {
                $errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format.';
            } else {
                $email = trim($email);
                if (strlen($email) > 255) {
                    $errors['email'] = 'Email address is too long.';
                } elseif ($email !== $user->getEmail()) {
                    $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
                    if ($existingUser) {
                        $errors['email'] = 'Email already registered.';
                    }
                }
            }

            // Nom/Prenom Validation (making them required and adding length constraints)
            if (empty($_POST['nom'])) { // Assuming 'nom' comes from $_POST
                $errors['nom'] = 'Last name is required.';
            } else {
                $nom = trim($_POST['nom']);
                if (strlen($nom) < 2) {
                    $errors['nom'] = 'Last name must be at least 2 characters long.';
                } elseif (strlen($nom) > 100) {
                    $errors['nom'] = 'Last name cannot exceed 100 characters.';
                } elseif (!preg_match('/^[a-zA-Z\s\'\-]+$/u', $nom)) {
                    $errors['nom'] = 'Last name can only contain letters, spaces, apostrophes, and hyphens.';
                }
            }

            if (empty($_POST['prenom'])) { // Assuming 'prenom' comes from $_POST
                $errors['prenom'] = 'First name is required.';
            } else {
                $prenom = trim($_POST['prenom']);
                if (strlen($prenom) < 2) {
                    $errors['prenom'] = 'First name must be at least 2 characters long.';
                } elseif (strlen($prenom) > 100) {
                    $errors['prenom'] = 'First name cannot exceed 100 characters.';
                } elseif (!preg_match('/^[a-zA-Z\s\'\-]+$/u', $prenom)) {
                    $errors['prenom'] = 'First name can only contain letters, spaces, apostrophes, and hyphens.';
                }
            }


            // --- Password Validation ---

            // Current Password is required for any change
            if (empty($currentPassword)) {
                $errors['current_password'] = 'Current password is required to save changes.';
            } elseif (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $errors['current_password'] = 'The current password you entered is incorrect.';
            }

            // New Password Validation (only if a new password is being set)
            if (!empty($newPassword)) {
                $newPassword = trim($newPassword);
                // Basic Strength Check (e.g., minimum length)
                if (strlen($newPassword) < 8) {
                    $errors['password'] = 'New password must be at least 8 characters long.';
                } elseif (strlen($newPassword) > 255) {
                    $errors['password'] = 'New password cannot exceed 255 characters.';
                } elseif (!preg_match('/[A-Z]/', $newPassword)) {
                    $errors['password'] = 'New password must include at least one uppercase letter.';
                } elseif (!preg_match('/[a-z]/', $newPassword)) {
                    $errors['password'] = 'New password must include at least one lowercase letter.';
                } elseif (!preg_match('/[0-9]/', $newPassword)) {
                    $errors['password'] = 'New password must include at least one number.';
                } elseif (!preg_match('/[\W_]/', $newPassword)) {
                    $errors['password'] = 'New password must include at least one special character (e.g., !@#$%^&*).';
                }


                if (empty($confirmPassword)) {
                    $errors['confirm_password'] = 'Please confirm your new password.';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors['password'] = 'The new passwords do not match.';
                    $errors['confirm_password'] = 'The new passwords do not match.'; // Add error to confirm_password field too
                }
            } elseif (!empty($confirmPassword) && empty($newPassword)) {
                // Edge case: User filled confirm but not the new password field
                $errors['password'] = 'Please enter the new password if you wish to change it.';
                $errors['confirm_password'] = 'Please enter the new password if you wish to change it.'; // Keep consistent
            }

            // Handle Avatar Upload
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Use slugger for safe filename
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                // Move the file to the directory where avatars are stored
                $avatarFile->move(
                    $this->getParameter('avatars_directory'), // Defined in services.yaml or framework.yaml
                    $newFilename
                );

                // Optional: Delete the old avatar file if it exists
                $oldAvatar = $user->getProfilePicture();
                if ($oldAvatar && file_exists($this->getParameter('avatars_directory') . '/' . $oldAvatar)) {
                    @unlink($this->getParameter('avatars_directory') . '/' . $oldAvatar);
                }

                // Update the 'avatar' property to store the new filename
                $user->setProfilePicture($newFilename); // Updated from setAvatar
            }


            // 4. Process Request IF NO ERRORS
            if (empty($errors)) {
                try {
                    // Handle Avatar Upload
                    if ($avatarFile) {
                        $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                        // Use slugger for safe filename
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                        // Move the file to the directory where avatars are stored
                        $avatarFile->move(
                            $this->getParameter('avatars_directory'), // Defined in services.yaml or framework.yaml
                            $newFilename
                        );

                        // Optional: Delete the old avatar file if it exists
                        $oldAvatar = $user->getProfilePicture();
                        if ($oldAvatar && file_exists($this->getParameter('avatars_directory') . '/' . $oldAvatar)) {
                            @unlink($this->getParameter('avatars_directory') . '/' . $oldAvatar);
                        }

                        // Update the 'avatar' property to store the new filename
                        $user->setProfilePicture($newFilename);
                    }

                    // Update User Properties
                    $user->setUsername($username);
                    $user->setEmail($email);
                    $user->setNom($nom);
                    $user->setPrenom($prenom);

                    // Update password ONLY if a new one was provided and validated
                    if (!empty($newPassword)) {
                        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                        $user->setPassword($hashedPassword);
                    }

                    // Persist Changes
                    $entityManager->flush();

                    $this->addFlash('success', 'Your profile has been updated successfully.');
                    return $this->redirectToRoute('app_profile'); // Redirect to a profile view page

                } catch (FileException $e) {
                    // Log the error: $this->logger->error('Avatar upload failed: '.$e->getMessage());
                    $this->addFlash('error', 'An error occurred during avatar upload. Please try again.');
                    // Add the error to the array to re-render the form
                    $errors['avatar'] = 'Avatar upload failed.';
                    // Fall through to render the form with errors
                } catch (\Exception $e) {
                    // Log the error: $this->logger->error('Profile update failed: '.$e->getMessage());
                    $this->addFlash('error', 'An unexpected error occurred. Please try again.');
                    // Add a generic error if needed
                    $errors['general'] = 'Could not save profile due to a server error.';
                    // Fall through to render the form with errors
                }
            }
            // If we reach here and $errors is not empty, the form will be re-rendered below
        }

        // 5. Render the form (GET request or POST with errors)
        return $this->render('front_office/user/edit_profile.html.twig', [
            'user' => $user,
            'errors' => $errors, // Pass the errors array to Twig
            // Keep the old variables if you still need them for *initial* rendering,
            // but errors from POST will be in the 'errors' array.
            // It's cleaner to rely solely on the 'errors' array.
            // 'usernameError' => $errors['username'] ?? null,
            // 'passwordError' => $errors['password'] ?? $errors['confirm_password'] ?? null, // Combine?
            // 'currentPasswordError' => $errors['current_password'] ?? null,
        ]);
    }
}
