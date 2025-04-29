<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class ChatbotService
{
    private HttpClientInterface $httpClient;
    private string $apiUrl = 'https://openrouter.ai/api/v1';
    private string $apiKey = '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'; // Hardcoded API key
    private ?LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient, 
        LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function getChatbotResponse(string $userMessage): string
    {
        try {
            $this->logDebug("Sending request to OpenRouter API with message: " . substr($userMessage, 0, 50) . "...");
            
            // Create request to OpenRouter API
            $response = $this->httpClient->request('POST', $this->apiUrl . '/chat/completions', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'HTTP-Referer' => 'https://your-app-domain.com', // Optional but recommended for OpenRouter
                ],
                'json' => [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $userMessage
                        ]
                    ],
                    'model' => 'deepseek/deepseek-chat', // Use the correct model name for DeepSeek on OpenRouter
                    'max_tokens' => 1000,
                    'temperature' => 0.7,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent();
            
            // Log the response for debugging
            $this->logDebug("API Response Status: $statusCode");
            $this->logDebug("API Response: " . substr($content, 0, 200) . "...");

            // Check if response is SVG/XML
            if (strpos($content, '<path') !== false || strpos($content, '<svg') !== false) {
                return "Received SVG content. Please check API configuration. The API is returning graphics instead of text.";
            }
            
            // Parse the JSON response
            $jsonResponse = json_decode($content, true);
            
            // Extract response from OpenRouter format
            if (isset($jsonResponse['choices']) && !empty($jsonResponse['choices'])) {
                $firstChoice = $jsonResponse['choices'][0];
                if (isset($firstChoice['message']['content'])) {
                    return $firstChoice['message']['content'];
                }
            }
            
            // Check for error response
            if (isset($jsonResponse['error']['message'])) {
                return "API Error: " . $jsonResponse['error']['message'];
            }
            
            // Fallback parsing methods
            if (isset($jsonResponse['response'])) {
                return $jsonResponse['response'];
            } elseif (isset($jsonResponse['message'])) {
                return $jsonResponse['message'];
            } elseif (isset($jsonResponse['text'])) {
                return $jsonResponse['text'];
            } elseif (isset($jsonResponse['content'])) {
                return $jsonResponse['content'];
            } else {
                return $this->cleanJsonOutput(json_encode($jsonResponse));
            }
        } catch (\Exception $e) {
            $this->logError("Error connecting to chatbot service: " . $e->getMessage());
            return "Error connecting to the chatbot service: " . $e->getMessage();
        }
    }

    /**
     * Extracts meaningful text from HTML response
     */
    private function extractTextFromHtml(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $extractedText = '';
        
        // Look for text within <p> tags first
        preg_match_all('/<p[^>]*>(.*?)<\/p>/s', $html, $pMatches);
        foreach ($pMatches[1] as $pContent) {
            $trimmedContent = trim($pContent);
            if (!empty($trimmedContent)) {
                $extractedText .= $trimmedContent . "\n\n";
            }
        }
        
        // If no <p> tags found with content, try to extract from <div> tags
        if (empty($extractedText)) {
            preg_match_all('/<div[^>]*>(.*?)<\/div>/s', $html, $divMatches);
            foreach ($divMatches[1] as $divContent) {
                $trimmedContent = trim($divContent);
                if (!empty($trimmedContent) && strpos($trimmedContent, '<') === false) {
                    $extractedText .= $trimmedContent . "\n";
                }
            }
        }
        
        // If still no content, look for any text that might be meaningful
        if (empty($extractedText)) {
            // Remove all HTML tags
            $textOnly = preg_replace('/<[^>]*>/', ' ', $html);
            // Remove excess whitespace
            $textOnly = preg_replace('/\s+/', ' ', $textOnly);
            $textOnly = trim($textOnly);
            
            if (!empty($textOnly)) {
                // Return a reasonable amount of the text
                return strlen($textOnly) > 1000 ? 
                    substr($textOnly, 0, 1000) . '... (content truncated)' : 
                    $textOnly;
            }
        }
        
        $result = trim($extractedText);
        if (empty($result)) {
            return "Received HTML response but couldn't extract meaningful text. Please check the API endpoint.";
        }
        
        return $result;
    }

    /**
     * Cleans up JSON output to make it more readable
     */
    private function cleanJsonOutput(string $jsonStr): string
    {
        $jsonStr = preg_replace('/[{}"]/','', $jsonStr);
        $jsonStr = str_replace(',', ', ', $jsonStr);
        $jsonStr = str_replace(':', ': ', $jsonStr);
        return $jsonStr;
    }
    
    /**
     * Log debug messages
     */
    private function logDebug(string $message): void
    {
        if ($this->logger) {
            $this->logger->debug('[Chatbot] ' . $message);
        } else {
            // Fallback to error_log if no logger is injected
            error_log('[Chatbot Debug] ' . $message);
        }
    }
    
    /**
     * Log error messages
     */
    private function logError(string $message): void
    {
        if ($this->logger) {
            $this->logger->error('[Chatbot] ' . $message);
        } else {
            // Fallback to error_log if no logger is injected
            error_log('[Chatbot Error] ' . $message);
        }
    }
}