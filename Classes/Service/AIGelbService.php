<?php

declare(strict_types=1);

namespace IGelb\Aigelb\Service;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RequestFactory;

#[Channel('aigelb-service')]
final readonly class AIGelbService {
    public function __construct(
        protected readonly LoggerInterface $logger,
        private readonly ConnectionPool $connectionPool,
        protected readonly RequestFactory $requestFactory,
    ) {}

    public function getAgentId(string $baseUrl): string {
        // in case we already have an agentId set in Directus we transfer it through .env
        if (getenv('AIGELB_AGENTID')) {
            return getenv('AIGELB_AGENTID');
        }

        $additionalOptions = [
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('AIGELB_API_TOKEN'),
            ],
            'body' => '{"url": "' . $baseUrl . '"}',
        ];

        try {
            $response = $this->requestFactory->request(
                getenv('AIGELB_API_URL') . '/api/agent',
                'POST',
                $additionalOptions
            );

            $contents = json_decode($response->getBody()->getContents());
            $this->logger->info('Fetch AgentID with token' . getenv('AIGELB_API_TOKEN') . ' and received ' . $contents->id);
            return $contents->id;
        } catch (\Exception $e) {
            $this->logger->error('Fetch AgentID with token' . getenv('AIGELB_API_TOKEN') . ' and received error ' . $e->getMessage());
            return '';
        }
    }

    public function addKnowledge(string $agentId, string $url, string $promptRequirements): string {
        $additionalOptions = [
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('AIGELB_API_TOKEN'),
            ],
            'body' => '{"type": "page"'
                . ', "context": "' . $promptRequirements
                . '", "source": "' . $url
                . '"'
                . '}',
        ];

        try {
            $response = $this->requestFactory->request(
                getenv('AIGELB_API_URL') . '/api/agent/' . $agentId . '/knowledge',
                'POST',
                $additionalOptions
            );

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            $this->logger->error('Tried to add knowledge with agentId ' . $agentId . ' and received error ' . $e->getMessage());
            return '';
        }
    }

    /**
     * @outdated - no longer in use as such - 250313
     */
    public function updateAgent(string $agentId, int $pageUid, string $promptRequirements, string $knowledgeBase, string $language): int {
        $additionalOptions = [
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('AIGELB_API_TOKEN'),
            ],
            'body' => '{"prompt_requirements": "'
                . $promptRequirements
                . '", "knowledge_base": ' . $knowledgeBase
                . ', "language": "' . $language
                . '", "page_id": "' . $pageUid
                . '"'
                . '}',
        ];

        try {
            $response = $this->requestFactory->request(
                getenv('AIGELB_API_URL') . '/api/agent/update/' . $agentId,
                'PUT',
                $additionalOptions
            );

            return $response->getStatusCode();

        } catch (\Exception $e) {
            $this->logger->error('Tried to update page uid ' . $pageUid . ' and received error ' . $e->getMessage());
            return 500;
        }
    }

    public function streamAgent(string $agentId, string $message, string $language): string {
        $additionalOptions = [
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('AIGELB_API_TOKEN'),
            ],
            'body' => '{"message": "' . $message . '", "language": "' . $language . '"}',
            'content-type' => 'application/json',
        ];

        try {
            $response = $this->requestFactory->request(
                getenv('AIGELB_RAG_URL') . '/api/stream/' . $agentId,
                'POST',
                $additionalOptions
            );

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
            $this->logger->error('Tried to stream with agentId ' . $agentId . ' and message #' . $message . '# and received error ' . $e->getMessage());
            return '';
        }
    }

    public function hasAgent(): string {
        $result = $this->connectionPool
            ->getConnectionForTable('tt_content')
            ->select(
                ['agentId'],
                'tx_aigelb_domain_model_agent',
                [],
            )
            ->fetchAssociative();

        if ($result === false) {
            return '';
        }

        return $result['agentId'];
    }

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
