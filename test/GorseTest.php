<?php

use Gorse\Gorse;
use Gorse\Dto\Feedback;
use Gorse\Dto\User;
use Gorse\Gorse\RowDetected;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

final class GorseTest extends TestCase
{
    const ENDPOINT = "http://127.0.0.1:8088/";
    const API_KEY = "zhenghaoz";

    /**
     * @throws GuzzleException
     */
    public function testUsers(): void
    {
        $client = new Gorse(self::ENDPOINT, self::API_KEY);
        $user = new User("1", array("a", "b", "c"));
        // Insert a user.
        $rowsAffected = $client->insertUser($user);
        $this->assertEquals(1, $rowsAffected->rowAffected);
        // Get this user.
        $returnUser = $client->getUser("1");
        $this->assertEquals($user, $returnUser);
        // Delete this user.
        $rowsAffected = $client->deleteUser("1");
        $this->assertEquals(1, $rowsAffected->rowAffected);
        try {
            $client->getUser("1");
            $this->fail();
        } catch (ClientException $exception) {
            $this->assertEquals(404, $exception->getCode());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testFeedback()
    {
        $client = new Gorse(self::ENDPOINT, self::API_KEY);
        $feedback = [
            new Feedback("read", "10", "3", "2022-11-20T13:55:27Z"),
            new Feedback("read", "10", "4", "2022-11-20T13:55:27Z"),
        ];
        $rowsAffected = $client->insertFeedback($feedback);
        $this->assertEquals(2, $rowsAffected->rowAffected);
    }

    /**
     * @throws RedisException|GuzzleException
     */
    public function testRecommend()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $redis->zAdd('offline_recommend/10', [], 1, '10', 2, '20', 3, '30');
        $client = new Gorse(self::ENDPOINT, self::API_KEY);
        $items = $client->getRecommend('10');
        $this->assertEquals(['30', '20', '10'], $items);
    }
}
