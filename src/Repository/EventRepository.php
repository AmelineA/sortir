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



    public function listEventsBySite(User $user)
    {
        $site=$user->getSite();
        $qb=$this->createQueryBuilder('e');
        $qb->andWhere('e.site=:site');
        $qb->setParameter('site', $site);
        $query=$qb->getQuery();
        return $query->getResult();
    }



    public function findListEventsBy($user, $site, $signedOn)
    {
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('e');


        //liste les events dont le user est l'organisateur

        $qb->andWhere('e.organizer=:user');
        $qb->setParameter('user', $user);


        //liste les events déjà passés

        $qb->andWhere('e.rdvTime<:today');
        $qb->setParameter('today', $today);


        //liste les events par site

        $qb->andWhere('e.site=:site');
        $qb->setParameter('site', $site);


        //liste les events entre date et date

        $qb->andWhere('e.rdvTime>$dateStart');
        $qb->andWhere('e.rdvTime<$dateEnd');
        $qb->setParameter('dateStart', $dateStart);
        $qb->setParameter('dateEnd', $dateEnd);

        $query = $qb->getQuery();
        return null;
    }




    public function alreadySignedOn(User $user, $idEvent)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.participants', 'p')
            ->addSelect('p')
            ->andWhere('e.id = :idEvent')
            ->andWhere('p. = :idUser')
            ->setParameters([
                'idEvent' => $idEvent,
                'idUser' => $user->getId()
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
