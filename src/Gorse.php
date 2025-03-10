<?php declare(strict_types=1);

namespace Gorse;

use Gorse\Dto\Feedback;
use Gorse\Dto\RowAffected;
use Gorse\Dto\User;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

final readonly class Gorse
{
    public function __construct(
        private string $endpoint,
        private string $apiKey
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function insertUser(User $user): RowAffected
    {
        return RowAffected::fromJSON($this->request('POST', '/api/user/', $user));
    }

    /**
     * @throws GuzzleException
     */
    public function getUser(string $userId): User
    {
        return User::fromJSON($this->request('GET', '/api/user/' . $userId));
    }

    /**
     * @throws GuzzleException
     */
    public function deleteUser(string $userId): RowAffected
    {
        return RowAffected::fromJSON($this->request('DELETE', '/api/user/' . $userId));
    }

    /**
     * @param Feedback|Feedback[] $feedback
     * @throws GuzzleException
     */
    public function insertFeedback(mixed $feedback): RowAffected
    {
        if ($feedback instanceof Feedback) {
            $feedback = [$feedback];
        }

        return RowAffected::fromJSON($this->request('POST', '/api/feedback/', $feedback));
    }

    /**
     * @throws GuzzleException
     */
    public function getRecommend(string $userId): object
    {
        return $this->request('GET', '/api/recommend/' . $userId);
    }

    /**
     * @throws GuzzleException
     */
    private function request(string $method, string $uri, \JsonSerializable|array|null $body = null): object|null
    {
        $client = new Client(['base_uri' => $this->endpoint]);
        $options = [RequestOptions::HEADERS => ['X-API-Key' => $this->apiKey]];
        if ($body != null) {
            $options[RequestOptions::JSON] = $body;
        }
        $response = $client->request($method, $uri, $options);

        return json_decode($response->getBody()->getContents(), false);
    }
}