<?php
declare(strict_types=1);

namespace App\Message;

use App\DataFixtures\MessageStatusEnum;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
/**
 * TODO: Cover with a test
 */
class SendMessageHandler
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }
    
    public function __invoke(SendMessage $sendMessage): void
    {
        $message = new Message($sendMessage->text);
        $message->setStatus(MessageStatusEnum::SENT);

        $this->manager->persist($message);
        $this->manager->flush();
    }
}