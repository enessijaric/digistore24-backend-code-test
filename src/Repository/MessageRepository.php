<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @param array<string, string|null> $filters Array of filters where keys are field names and values are their filter values.
     * @return Message[]
     */
    public function by(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('m');

        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $qb->andWhere($qb->expr()->eq('m.' . $field, ':' . $field))
                    ->setParameter($field, $value);
            }
        }

        /** @var Message[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;    }
}
