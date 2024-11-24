<?php
namespace App\DataFixtures;

use App\Entity\Message;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $statuses = MessageStatusEnum::cases();

        foreach (range(1, 10) as $i) {
            $numberOfWords = $faker->numberBetween(4, 8);

            $messageText = "Message $i: " . implode(' ', (array) $faker->words($numberOfWords));
            $message = new Message($messageText);

            $status = $statuses[$i % count($statuses)];
            $message->setStatus($status);

            $manager->persist($message);
        }

        $manager->flush();
    }
}
