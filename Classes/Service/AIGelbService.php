<?php

declare(strict_types=1);

namespace IGelb\Aigelb\Service;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;

#[Channel('aigelb-service')]
final readonly class AIGelbService {
    private const API_TOKEN_ENV = 'AIGELB_API_TOKEN';
    private const API_URL_ENV = 'AIGELB_API_URL';
    private const RAG_URL_ENV = 'AIGELB_RAG_URL';
    private const AGENT_ID_ENV = 'AIGELB_AGENTID';

    public function __construct(
        protected readonly LoggerInterface $logger,
        private readonly ConnectionPool $connectionPool,
        protected readonly RequestFactory $requestFactory,
    ) {}

    /**
     * Returns the API token from the environment variable
     * @throws \RuntimeException If the token is not found
     */
    private function getApiToken(): string {
        $token = getenv(self::API_TOKEN_ENV);
        if (!$token) {
            throw new \RuntimeException('API Token not found in environment variables');
        }
        return $token;
    }

    /**
     * Creates the default headers for API requests
     */
    private function getDefaultHeaders(): array {
        return [
            'Authorization' => 'Bearer ' . $this->getApiToken(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Sends a request to the API and returns the response
     * @throws \Exception On API request error
     */
    private function sendApiRequest(string $url, string $method, array $data): Response {
        $options = [
            'headers' => $this->getDefaultHeaders(),
            'body' => json_encode($data),
        ];
        
        return $this->requestFactory->request($url, $method, $options);
    }

    /**
     * Retrieves the Agent ID from the environment or the API
     */
    public function getAgentId(string $baseUrl): string {
        // In case we already have an agentId set in Directus we transfer it through .env
        if ($agentId = getenv(self::AGENT_ID_ENV)) {
            return $agentId;
        }

        try {
            $apiUrl = getenv(self::API_URL_ENV) . '/api/agent';
            $response = $this->sendApiRequest($apiUrl, 'POST', ['url' => $baseUrl]);
            
            $contents = json_decode($response->getBody()->getContents());
            $this->logger->info('Agent ID fetched successfully', ['id' => $contents->id]);
            
            return $contents->id;
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch Agent ID', [
                'error' => $e->getMessage(),
                'baseUrl' => $baseUrl
            ]);
            return '';
        }
    }

    /**
     * Adds knowledge to the agent
     */
    public function addKnowledge(string $agentId, string $url, string $promptRequirements): string {
        try {
            $apiUrl = getenv(self::API_URL_ENV) . '/api/agent/' . $agentId . '/knowledge';
            $data = [
                'type' => 'page',
                'context' => $promptRequirements,
                'source' => $url
            ];
            
            $response = $this->sendApiRequest($apiUrl, 'POST', $data);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            $this->logger->error('Failed to add knowledge', [
                'agentId' => $agentId,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Streams a message to the agent
     */
    public function streamAgent(string $agentId, string $message, string $language): string {
        try {
            $apiUrl = getenv(self::RAG_URL_ENV) . '/api/stream/' . $agentId;
            $data = [
                'message' => $message,
                'language' => $language
            ];
            
            $response = $this->sendApiRequest($apiUrl, 'POST', $data);
            
            // Get the stream body
            $stream = $response->getBody();
            $result = '';

            // Read the stream in chunks
            while (!$stream->eof()) {
                $chunk = $stream->read(4096); // Read 4KB at a time
                if ($chunk !== '') {
                    $result .= $chunk;
                }
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to stream agent', [
                'agentId' => $agentId,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Checks if an agent exists and returns its ID
     */
    public function hasAgent(): string {
        $result = $this->connectionPool
            ->getConnectionForTable('tt_content')
            ->select(
                ['agentId'],
                'tx_aigelb_domain_model_agent',
                [],
            )
            ->fetchAssociative();

        return $result === false ? '' : $result['agentId'];
    }

    /**
     * Saves an Agent ID in the database
     */
    public function saveAgentId(string $agentId): void {
        $this->connectionPool
            ->getConnectionForTable('tt_content')
            ->insert(
                'tx_aigelb_domain_model_agent',
                [
                    'agentId' => $agentId,
                    'crdate' => time(),
                ],
            );
    }
}