<?php
// src/Controller/back_office/TransportModeController.php
namespace App\Controller\back_office;

use App\Service\TransportModeService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/transport-modes')]
class TransportModeController extends AbstractController
{
    #[Route('', name: 'admin_transport_modes')]
    public function index(TransportModeService $transportModeService): Response
    {
        $modes = $transportModeService->getAllTransportModes();
        
        return $this->render('back_office/transport_modes/index.html.twig', [
            'modes' => $modes
        ]);
    }

    #[Route('/get-all', name: 'admin_transport_modes_get_all', methods: ['GET'])]
    public function getAllModes(TransportModeService $transportModeService): JsonResponse
    {
        $modes = $transportModeService->getAllTransportModes();
        
        return $this->json($modes);
    }

    #[Route('/create', name: 'admin_transport_modes_create', methods: ['POST'])]
    public function createMode(
        Request $request, 
        TransportModeService $transportModeService,
        LoggerInterface $logger  // Add this parameter
    ): JsonResponse {
        $logger->info('Create transport mode request received');
        $logger->info('Request content: ' . $request->getContent());
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }
        
        $logger->info('Attempting to create transport mode with name: ' . $data['name']);
        $success = $transportModeService->createTransportMode($data['name']);
        $logger->info('Result of transport mode creation: ' . ($success ? 'success' : 'failure'));
        
        return $this->json(['success' => $success]);
    }

    #[Route('/update/{id}', name: 'admin_transport_modes_update', methods: ['PUT'])]
    public function updateMode(
        int $id, 
        Request $request, 
        TransportModeService $transportModeService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }
        
        $success = $transportModeService->updateTransportMode($id, $data['name']);
        
        if (!$success) {
            return $this->json(['error' => 'Transport mode not found'], 404);
        }
        
        return $this->json(['success' => true]);
    }

    #[Route('/delete/{id}', name: 'admin_transport_modes_delete', methods: ['DELETE'])]
    public function deleteMode(
        int $id, 
        TransportModeService $transportModeService
    ): JsonResponse {
        $success = $transportModeService->deleteTransportMode($id);
        
        if (!$success) {
            return $this->json(['error' => 'Transport mode not found'], 404);
        }
        
        return $this->json(['success' => true]);
    }
}