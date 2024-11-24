<?php
declare(strict_types=1);

namespace App\Controller;

use App\DataFixtures\MessageStatusEnum;
use App\Entity\Message;
use App\Message\SendMessage;
use App\Repository\MessageRepository;
use Controller\MessageControllerTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @see MessageControllerTest
 */
class MessageController extends AbstractController
{
    /**
     * TODO: cover this method with tests, and refactor the code (including other files that need to be refactored)
     */
    #[Route('/messages')]
    public function list(Request $request, MessageRepository $messageRepository): Response
    {
        $filters = [];
        $status = $request->query->get('status');
        if ($status !== null && !in_array($status, array_column(MessageStatusEnum::cases(), 'value'), true)) {
            return $this->json(['error' => 'Invalid status value'], Response::HTTP_BAD_REQUEST);
        }

        $filters['status'] = $status;
        $messages = $messageRepository->by($filters);

        $response = array_map(static fn($message) => [
            'uuid' => $message->getUuid(),
            'text' => $message->getText(),
            'status' => $message->getStatus(),
        ], $messages);

        return $this->json(['messages' => $response]);
    }

    #[Route('/message/send', methods: ['POST'])]
    public function send(Request $request, MessageBusInterface $bus): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || empty($data['text']) || !is_string($data['text'])) {
            return new JsonResponse(['error' => 'Invalid text parameter. Must be a non-empty string.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $message = new Message($data['text']);

            $bus->dispatch(new SendMessage($message->getText()));

            return new JsonResponse(['message' => 'Successfully sent'], Response::HTTP_NO_CONTENT);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}