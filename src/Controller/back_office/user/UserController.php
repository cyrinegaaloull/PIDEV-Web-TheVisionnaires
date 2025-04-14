<?php

namespace App\Controller\back_office\user;

use App\Entity\Users;
use App\Entity\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


function safeFilename($filename) {
    // Replace accented characters with ASCII equivalents
    $filename = preg_replace('/[áàâäãåąæā]/ui', 'a', $filename);
    $filename = preg_replace('/[éèêëęėē]/ui', 'e', $filename);
    $filename = preg_replace('/[íìîïįī]/ui', 'i', $filename);
    $filename = preg_replace('/[óòôöõøőō]/ui', 'o', $filename);
    $filename = preg_replace('/[úùûüųū]/ui', 'u', $filename);
    $filename = preg_replace('/[çćč]/ui', 'c', $filename);
    $filename = preg_replace('/[żźž]/ui', 'z', $filename);
    $filename = preg_replace('/[śš]/ui', 's', $filename);
    $filename = preg_replace('/[ñń]/ui', 'n', $filename);
    $filename = preg_replace('/[ýÿ]/ui', 'y', $filename);
    
    // Remove any character that is not a letter, number or underscore
    $filename = preg_replace('/[^a-z0-9_]/i', '', $filename);
    
    // Convert to lowercase
    return strtolower($filename);
}


class UserController extends AbstractController
{
    #[Route('/add-user', name: 'add_user', methods: ['GET', 'POST'])]
    public function addUser(
        Request $request,
        EntityManagerInterface $entityManager
        // Removed: UserPasswordHasherInterface $passwordHasher
    ): Response {
        $errors = []; // Array to hold validation errors
        $submittedData = []; // Array to hold submitted data for re-populating the form

        if ($request->isMethod('POST')) {
            // Basic CSRF check
            if (!$this->isCsrfTokenValid('user_item', $request->request->get('_token'))) {
                $errors['formError'] = 'Invalid security token. Please try submitting again.';
                $roles = $entityManager->getRepository(Roles::class)->findAll();
                return $this->render('back_office/user/add_user.html.twig', [
                    'roles' => $roles,
                    'errors' => $errors,
                    'username' => $request->request->get('username'),
                    'email' => $request->request->get('email'),
                    'nom' => $request->request->get('nom'),
                    'prenom' => $request->request->get('prenom'),
                    'role_id' => $request->request->get('role_id'),
                ]);
            }

            // Retrieve form fields
            $username = trim($request->request->get('username', ''));
            $email = trim($request->request->get('email', ''));
            $password = $request->request->get('password', ''); // Plain text password
            $confirmPassword = $request->request->get('confirm_password', '');
            $nom = trim($request->request->get('nom', ''));
            $prenom = trim($request->request->get('prenom', ''));
            $avatarFile = $request->files->get('avatar');
            $roleId = $request->request->get('role_id', '');

            // Store submitted data (excluding password for security in re-rendering)
            $submittedData = [
                'username' => $username,
                'email' => $email,
                'nom' => $nom,
                'prenom' => $prenom,
                'role_id' => $roleId,
            ];

            // --- Start Validation ---

            // 1. All inputs filled (basic check)
            if (empty($username)) $errors['usernameError'] = 'Username is required.';
            if (empty($email)) $errors['emailError'] = 'Email is required.';
            if (empty($password)) $errors['passwordError'] = 'Password is required.';
            if (empty($confirmPassword)) $errors['confirmPasswordError'] = 'Password confirmation is required.';
            if (empty($nom)) $errors['nomError'] = 'Last name (Nom) is required.';
            if (empty($prenom)) $errors['prenomError'] = 'First name (Prenom) is required.';
            if (!$avatarFile) $errors['avatarError'] = 'Avatar image is required.'; // Adjust if optional
            if (empty($roleId)) $errors['roleIdError'] = 'Role is required.';

            // 2. Username unique
            if (!isset($errors['usernameError']) && !empty($username)) {
                $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
                if ($existingUser) {
                    $errors['usernameError'] = 'This username is already taken.';
                }
            }

            // 3. Email format
            if (!isset($errors['emailError']) && !empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['emailError'] = 'Invalid email format.';
            }

            // 4. Password complexity
            if (!isset($errors['passwordError']) && !empty($password)) {
                 if (strlen($password) < 8) {
                     $errors['passwordError'] = 'Password must be at least 8 characters long.';
                 } elseif (!preg_match('/[A-Z]/', $password)) {
                     $errors['passwordError'] = 'Password must include at least one uppercase letter.';
                 } elseif (!preg_match('/[a-z]/', $password)) {
                      $errors['passwordError'] = 'Password must include at least one lowercase letter.';
                 } elseif (!preg_match('/[0-9]/', $password)) {
                     $errors['passwordError'] = 'Password must include at least one number.';
                 }
            }

            // 5. Passwords match
             if (!isset($errors['passwordError']) && !isset($errors['confirmPasswordError']) && !empty($password) && $password !== $confirmPassword) {
                 $errors['confirmPasswordError'] = 'Passwords do not match.';
             }

            // 6. Nom validation (min 2 chars, no numbers)
            if (!isset($errors['nomError']) && !empty($nom)) {
                if (strlen($nom) < 2) {
                    $errors['nomError'] = 'Last name (Nom) must be at least 2 characters long.';
                } elseif (preg_match('/[0-9]/', $nom)) {
                    $errors['nomError'] = 'Last name (Nom) cannot contain numbers.';
                }
            }

            // 7. Prenom validation (min 2 chars, no numbers)
            if (!isset($errors['prenomError']) && !empty($prenom)) {
                if (strlen($prenom) < 2) {
                    $errors['prenomError'] = 'First name (Prenom) must be at least 2 characters long.';
                } elseif (preg_match('/[0-9]/', $prenom)) {
                    $errors['prenomError'] = 'First name (Prenom) cannot contain numbers.';
                }
            }

            // 8. Avatar file validation
            if (!isset($errors['avatarError']) && $avatarFile) {
                 $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                 $maxSize = 2 * 1024 * 1024; // 2MB

                 if ($avatarFile->getSize() > $maxSize) {
                     $errors['avatarError'] = 'Avatar file is too large (max 2MB).';
                 } elseif (!in_array($avatarFile->getMimeType(), $allowedMimes)) {
                      $errors['avatarError'] = 'Invalid file type. Only JPEG, PNG, GIF, WEBP allowed.';
                 }
            }

             // 9. Role ID validation
             $role = null;
             if (!isset($errors['roleIdError']) && !empty($roleId)) {
                 $role = $entityManager->getRepository(Roles::class)->find($roleId);
                 if (!$role) {
                     $errors['roleIdError'] = 'Invalid role selected.';
                 }
             }

            // --- End Validation ---

            // If errors, re-render form
            if (!empty($errors)) {
                $roles = $entityManager->getRepository(Roles::class)->findAll();
                $renderContext = array_merge(
                     ['roles' => $roles, 'errors' => $errors],
                     $submittedData
                );
                return $this->render('back_office/user/add_user.html.twig', $renderContext);
            }

            // --- Process if validation passes ---

            // Process avatar upload
            $avatarPath = null;
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = safeFilename($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                try {
                    $avatarFile->move($this->getParameter('avatars_directory'), $newFilename);
                    $avatarPath = $newFilename;
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload avatar: ' . $e->getMessage());
                     $roles = $entityManager->getRepository(Roles::class)->findAll();
                     $renderContext = array_merge(
                         ['roles' => $roles, 'errors' => ['avatarError' => 'Avatar upload failed.']],
                         $submittedData
                     );
                     return $this->render('back_office/user/add_user.html.twig', $renderContext);
                }
            }

            // Create and set up the new user entity
            $user = new Users();
            $user->setUsername($username);
            $user->setEmail($email);

            // --- SETTING PLAIN TEXT PASSWORD (HIGHLY INSECURE) ---
            $user->setPassword($password);
            // --- END OF INSECURE PASSWORD HANDLING ---

            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setAvatar($avatarPath);
            $user->setRole($role);

            // Persist and flush
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User added successfully!');
            return $this->redirectToRoute('list_users');

        } // End POST

        // --- GET Request Handling ---
        $roles = $entityManager->getRepository(Roles::class)->findAll();
        return $this->render('back_office/user/add_user.html.twig', [
            'roles' => $roles,
            'errors' => $errors,
            'username' => '',
            'email' => '',
            'nom' => '',
            'prenom' => '',
            'role_id' => '',
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
        $errors = []; // Array to hold validation errors
        
        if ($request->isMethod('POST')) {
            // Basic CSRF check
            if (!$this->isCsrfTokenValid('update_user' . $user->getUserId(), $request->request->get('_token'))) {
                $errors['formError'] = 'Invalid security token. Please try submitting again.';
                $roles = $entityManager->getRepository(Roles::class)->findAll();
                return $this->render('back_office/user/update_user.html.twig', [
                    'user' => $user,
                    'roles' => $roles,
                    'errors' => $errors
                ]);
            }
    
            // Retrieve form fields
            $username = trim($request->request->get('username', ''));
            $email = trim($request->request->get('email', ''));
            $password = $request->request->get('password', ''); // Plain text password
            $confirmPassword = $request->request->get('confirm_password', '');
            $nom = trim($request->request->get('nom', ''));
            $prenom = trim($request->request->get('prenom', ''));
            $avatarFile = $request->files->get('avatar');
            $roleId = $request->request->get('role_id', '');
    
            // --- Start Validation ---
    
            // 1. All inputs filled (basic check)
            if (empty($username)) $errors['usernameError'] = 'Username is required.';
            if (empty($email)) $errors['emailError'] = 'Email is required.';
            if (empty($password)) $errors['passwordError'] = 'Password is required.';
            if (empty($confirmPassword)) $errors['confirmPasswordError'] = 'Password confirmation is required.';
            if (empty($nom)) $errors['nomError'] = 'Last name (Nom) is required.';
            if (empty($prenom)) $errors['prenomError'] = 'First name (Prenom) is required.';
            if (empty($roleId)) $errors['roleIdError'] = 'Role is required.';
    
            // 2. Username unique (excluding current user)
            if (!isset($errors['usernameError']) && !empty($username)) {
                $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $username]);
                if ($existingUser && $existingUser->getUserId() !== $user->getUserId()) {
                    $errors['usernameError'] = 'This username is already taken.';
                }
            }
    
            // 3. Email format
            if (!isset($errors['emailError']) && !empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['emailError'] = 'Invalid email format.';
            }
    
            // 4. Password complexity
            if (!isset($errors['passwordError']) && !empty($password)) {
                if (strlen($password) < 8) {
                    $errors['passwordError'] = 'Password must be at least 8 characters long.';
                } elseif (!preg_match('/[A-Z]/', $password)) {
                    $errors['passwordError'] = 'Password must include at least one uppercase letter.';
                } elseif (!preg_match('/[a-z]/', $password)) {
                    $errors['passwordError'] = 'Password must include at least one lowercase letter.';
                } elseif (!preg_match('/[0-9]/', $password)) {
                    $errors['passwordError'] = 'Password must include at least one number.';
                }
            }
    
            // 5. Passwords match
            if (!isset($errors['passwordError']) && !isset($errors['confirmPasswordError']) && !empty($password) && $password !== $confirmPassword) {
                $errors['confirmPasswordError'] = 'Passwords do not match.';
            }
    
            // 6. Nom validation (min 2 chars, no numbers)
            if (!isset($errors['nomError']) && !empty($nom)) {
                if (strlen($nom) < 2) {
                    $errors['nomError'] = 'Last name (Nom) must be at least 2 characters long.';
                } elseif (preg_match('/[0-9]/', $nom)) {
                    $errors['nomError'] = 'Last name (Nom) cannot contain numbers.';
                }
            }
    
            // 7. Prenom validation (min 2 chars, no numbers)
            if (!isset($errors['prenomError']) && !empty($prenom)) {
                if (strlen($prenom) < 2) {
                    $errors['prenomError'] = 'First name (Prenom) must be at least 2 characters long.';
                } elseif (preg_match('/[0-9]/', $prenom)) {
                    $errors['prenomError'] = 'First name (Prenom) cannot contain numbers.';
                }
            }
    
            // 8. Avatar file validation (only if uploaded)
            if ($avatarFile) {
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 2 * 1024 * 1024; // 2MB
    
                if ($avatarFile->getSize() > $maxSize) {
                    $errors['avatarError'] = 'Avatar file is too large (max 2MB).';
                } elseif (!in_array($avatarFile->getMimeType(), $allowedMimes)) {
                    $errors['avatarError'] = 'Invalid file type. Only JPEG, PNG, GIF, WEBP allowed.';
                }
            }
    
            // 9. Role ID validation
            $role = null;
            if (!isset($errors['roleIdError']) && !empty($roleId)) {
                $role = $entityManager->getRepository(Roles::class)->find($roleId);
                if (!$role) {
                    $errors['roleIdError'] = 'Invalid role selected.';
                }
            }
    
            // --- End Validation ---
    
            // If errors, re-render form
            if (!empty($errors)) {
                $roles = $entityManager->getRepository(Roles::class)->findAll();
                return $this->render('back_office/user/update_user.html.twig', [
                    'user' => $user,
                    'roles' => $roles,
                    'errors' => $errors
                ]);
            }
    
            // --- Process if validation passes ---
    
            // Process avatar upload
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = safeFilename($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();
    
                try {
                    $avatarFile->move($this->getParameter('avatars_directory'), $newFilename);
                    $user->setAvatar($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload avatar: ' . $e->getMessage());
                    $roles = $entityManager->getRepository(Roles::class)->findAll();
                    return $this->render('back_office/user/update_user.html.twig', [
                        'user' => $user,
                        'roles' => $roles,
                        'errors' => ['avatarError' => 'Avatar upload failed.']
                    ]);
                }
            }
    
            // Update user entity
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($password); // Note: Still insecure plain text
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setRole($role);
    
            // Persist and flush
            $entityManager->flush();
    
            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('list_users');
        }
        
        // On GET, fetch roles and render the update form
        $roles = $entityManager->getRepository(Roles::class)->findAll();
        return $this->render('back_office/user/update_user.html.twig', [
            'user' => $user,
            'roles' => $roles,
            'errors' => []
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
