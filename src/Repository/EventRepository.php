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


    /**
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function listEventsBySite(User $user)
    {
        $today = new \DateTime();
        $interval= \DateInterval::createFromDateString("30 days");
        $day30=$today->sub($interval);

        $site=$user->getSite();
        $qb=$this->createQueryBuilder('e');
        $qb->andWhere('e.rdvTime>:day30');
        $qb->setParameter('day30', $day30);
        $qb->andWhere('e.site=:site');
        $qb->setParameter('site', $site);
        $query=$qb->getQuery();
        return $query->getResult();
    }


    /**
     * @param User $user
     * @param $site
     * @param $searchBar
     * @param $dateStart
     * @param $dateEnd
     * @param $organizer
     * @param $signedOn
     * @param $notSignedOn
     * @param $pastEvent
     * @return mixed
     * @throws \Exception
     */
    public function findListEventsBy(User $user, $site, $searchBar, $dateStart, $dateEnd, $organizer, $signedOn, $notSignedOn, $pastEvent)
    {
        $today = new \DateTime();
        $interval= \DateInterval::createFromDateString("30 days");
        $day30=$today->sub($interval);
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.participants', 'p');
     //   $qb->addSelect('p');

        $qb->andWhere('e.rdvTime>:day30');
        $qb->setParameter('day30', $day30);

//        //liste les events par site
//        if($site!==0){
//            $qb->andWhere('e.site=:site');
//            $qb->setParameter('site', $site);
//        }
//
//        //liste les events selon rechercher
//        if($searchBar!==""){
//            $qb->andWhere('e.name LIKE :searchBar');
//            $qb->setParameter('searchBar', '%'.$searchBar.'%');
//        }
//
//        //liste les events à partir de dateStart
//        if($dateStart!==""){
//            $qb->andWhere('e.rdvTime>:dateStart');
//            $qb->setParameter('dateStart', $dateStart);
//        }
//
//        //liste les events après dateEnd
//        if($dateEnd!==""){
//            $qb->andWhere('e.rdvTime<:dateEnd');
//            $qb->setParameter('dateEnd', $dateEnd);
//        }
//
//
//        //liste les events dont le user est l'organisateur
//        if($organizer==='on'){
//            $qb->andWhere('e.organizer=:user');
//            $qb->setParameter('user', $user);
//        }
//
//        //liste les events auxquels je suis inscrits
//        if($signedOn==='on'){
//            $qb->andWhere('p.id=:userId');
//            $qb->setParameter('userId', $user->getId());
//        }
//
//        //liste les events auxquels je ne suis PAS inscrits
//        if($notSignedOn==='on'){
//            $qb->andWhere('p.id!=:userId');
//            $qb->setParameter('userId', $user->getId());
//        }
//
//        //liste les events déjà passés
//        if($pastEvent==='on'){
//            $qb->andWhere('e.rdvTime<:today');
//            $qb->setParameter('today', $today);
//        }

        $query = $qb->getQuery();
        return $query->getResult();
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


    /*
     * Sert à ramener tous les événements à mettre à jour
     */
    public function updateState()
    {
        // récupération de la date d'aujourd'hui
        $now = new \DateTime();
        // création d'une date "aujourd'hui + 1 jour"
        $interval = \DateInterval::createFromDateString("1 day");
        $now->add($interval);
        $qb = $this->createQueryBuilder('e');
        // va chercher tous les évents dont le statut est "ouvert" et dont la date de fin d'inscription est passée
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
