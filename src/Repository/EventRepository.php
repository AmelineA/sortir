<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function alreadySignedOn(User $user, $idEvent)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.participants', 'p')
            ->addSelect('p')
            ->andWhere('e.id = :idEvent')
            ->andWhere('p.id = :idUser')
            ->setParameters([
                'idEvent' => $idEvent,
                'idUser' => $user->getId()
            ]);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function updateState()
    {
        $now = new \DateTime();
        $interval = \DateInterval::createFromDateString("1 day");
        $now->add($interval);
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.state = :state')
            ->andWhere('e.signOnDeadline < :now')
            ->setParameters([
                'state' => 'ouvert',
                'now' => $now
            ]);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    // /**
    //  * @return Event[] Returns an array of Event objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
