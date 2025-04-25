<?php
namespace App\Controller\front_office\user;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Psr\Log\LoggerInterface;
use App\Repository\UsersRepository; 
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



class FaceRecognitionLoginController extends AbstractController
{
    private $logger;
    private $eventDispatcher;
    private $tokenStorage;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
    }


    #[Route('/face-recognition-login', name: 'app_face_recognition_login')]
    public function faceRecognitionLogin(Request $request, UsersRepository $usersRepository, Security $security): JsonResponse
    {

        $this->logger->info('FaceRecognitionLoginController: Action started.');
        $pythonScriptPath = $this->getParameter('kernel.project_dir') . '/scripts/face_recognition_script.py'; // Adjust the path
        $photosFolderPath = $this->getParameter('kernel.project_dir') . '/scripts/'; // Assuming photos are in the same folder

        // Ensure the Python script exists
        if (!file_exists($pythonScriptPath)) {
            return new JsonResponse(['success' => false, 'message' => 'Error: Python script not found.']);
        }

        // Execute the Python script
        $process = new Process(['python', $pythonScriptPath]);
        $process->run();

        // Handle potential errors
        if (!$process->isSuccessful()) {
            return new JsonResponse(['success' => false, 'message' => 'Error during face recognition.']);
        }

        $output = $process->getOutput();
        $this->logger->info('FaceRecognitionLoginController: Python script output: ' . $output);
        // In your Python script, you should print the username if a face is recognized
        // For example: print("USERNAME_FOUND: john_doe")

        // Parse the output to get the recognized username (if any)
        if (preg_match('/USERNAME_FOUND: (\w+)/', $output, $matches)) {
            $username = $matches[1];
            $user = $usersRepository->findOneBy(['username' => $username]);

            if ($user) {
                // Authenticate the user programmatically
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $this->tokenStorage->setToken($token);
                
                // Dispatch the interactive login event
                $event = new InteractiveLoginEvent($request, $token);
                $this->eventDispatcher->dispatch($event);
            
                // Determine redirect URL based on roles
                $roles = $user->getRoles();
                if (in_array('ROLE_ADMIN', $roles)) {
                    $redirectUrl = $this->generateUrl('admin_dashboard');
                } else {
                    $redirectUrl = $this->generateUrl('app_home');
                }
            
                return new JsonResponse([
                    'success' => true, 
                    'message' => 'Face recognized. Logging in...', 
                    'redirect_url' => $redirectUrl
                ]);
            }}
    }
}