<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    private ChatbotService $chatbotService;
    
    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }
    
    /**
     * Display the chatbot interface
     */
    #[Route('/chatbot', name: 'app_chatbot')]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }
    
    /**
     * Handle chatbot messages via AJAX
     */
    #[Route('/chatbot/message', name: 'app_chatbot_message', methods: ['POST'])]
    public function message(Request $request): JsonResponse
    {
        // Get the user message from the request
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';
        
        if (empty($userMessage)) {
            return $this->json([
                'success' => false,
                'message' => 'No message provided'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Get response from chatbot service
        $response = $this->chatbotService->getChatbotResponse($userMessage);
        
        return $this->json([
            'success' => true,
            'message' => $response
        ]);
    }
}