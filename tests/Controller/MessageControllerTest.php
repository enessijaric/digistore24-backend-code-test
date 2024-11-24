<?php
declare(strict_types=1);

namespace Controller;

use App\DataFixtures\MessageStatusEnum;
use App\Entity\Message;
use App\Message\SendMessage;
use App\Message\SendMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MessageControllerTest extends WebTestCase
{
    use InteractsWithMessenger;

    public function test_list_with_no_status(): void
    {
        $client = static::createClient();
        $client->request('GET', '/messages');

        $this->assertResponseIsSuccessful();

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false.');

        $responseData = json_decode($responseContent, true);
        $this->assertNotNull($responseData, 'Response content should be a valid JSON.');
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('messages', $responseData);
        $this->assertIsArray($responseData['messages']);
    }

    public function test_list_with_valid_status(): void
    {
        $validStatus = MessageStatusEnum::SENT->value;
        $client = static::createClient();
        $client->request('GET', '/messages?status=' . $validStatus);

        $this->assertResponseIsSuccessful();

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false.');

        $responseData = json_decode($responseContent, true);
        $this->assertNotNull($responseData, 'Response content should be a valid JSON.');
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('messages', $responseData);
        $this->assertIsArray($responseData['messages']);

        foreach ($responseData['messages'] as $message) {
            $this->assertArrayHasKey('status', $message);
            $this->assertEquals($validStatus, $message['status']);
        }
    }

    public function test_list_with_invalid_status(): void
    {
        $invalidStatus = 'invalid_status';
        $client = static::createClient();
        $client->request('GET', '/messages?status=' . $invalidStatus);

        $this->assertResponseStatusCodeSame(400);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false.');

        $responseData = json_decode($responseContent, true);
        $this->assertNotNull($responseData, 'Response content should be a valid JSON.');
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid status value', $responseData['error']);
    }

    public function test_that_it_sends_a_message(): void
    {
        $client = static::createClient();
        $payload = ['text' => 'Hello World'];
        $jsonPayload = json_encode($payload);

        $this->assertNotFalse($jsonPayload, 'Failed to encode payload to JSON.');

        $client->request(
            'POST',
            '/message/send',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $jsonPayload
        );

        $this->assertResponseStatusCodeSame(204);

        $this->transport('sync')
            ->queue()
            ->assertCount(1)
            ->assertContains(SendMessage::class);
    }

    public function test_it_handles_sending_a_message(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager->expects($this->once())
        ->method('persist')
            ->with($this->callback(function (Message $message) {
                $status = $message->getStatus();
                return $message->getText() === 'Hello World'
                    && $status !== null
                    && $status->value === MessageStatusEnum::SENT->value;
            }));

        $entityManager->expects($this->once())
        ->method('flush');

        $handler = new SendMessageHandler($entityManager);

        $message = new SendMessage('Hello World');

        $handler($message);
    }

}