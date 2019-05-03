<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\Constraints\Date;

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
     * is used to list all the events of the user's site
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function listEventsBySite(User $user)
    {
        $today = new \DateTime();
        $interval = \DateInterval::createFromDateString("30 days");
        $day30 = $today->sub($interval);

        $site = $user->getSite();
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.rdvTime>:day30');
        $qb->setParameter('day30', $day30);
        $qb->andWhere('e.site=:site');
        $qb->setParameter('site', $site);
        $query = $qb->getQuery();
        return $query->getResult();
    }


    /**
     * is used to search for events depending on criterias selected by the user
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
    public function findListEventsBy(User $user, $site, $searchBar, $dateStart, $dateEnd,
                                     $organizer, $signedOn, $notSignedOn, $pastEvent)
    {
        $today = new \DateTime('now');
        $day30 = new \DateTime('now');
        $interval = \DateInterval::createFromDateString("30 days");
        $day30 = $day30->sub($interval);

        $qb = $this->createQueryBuilder('e');
        $qb->join('e.participants', 'p');
        //   $qb->addSelect('p');

        $qb->andWhere('e.rdvTime>:day30');
        $qb->setParameter('day30', $day30);

        //list the events depending on sites
        if ($site !== "0") {
            $qb->andWhere('e.site=:site');
            $qb->setParameter('site', $site);
        }

        //list the events depending on what the user typed in the searchbar
        if ($searchBar !== "") {
            $qb->andWhere('e.name LIKE :searchBar');
            $qb->setParameter('searchBar', '%' . $searchBar . '%');
        }

        //list the events happening after the date typed by the user
        if ($dateStart !== "") {
            $qb->andWhere('e.rdvTime>:dateStart');
            $qb->setParameter('dateStart', $dateStart);
        }

        //list the events happening before the date typed by the user
        if ($dateEnd !== "") {
            $qb->andWhere('e.rdvTime<:dateEnd');
            $qb->setParameter('dateEnd', $dateEnd);
        }


        //list the events organized by the user
        if ($organizer === 'on') {
            $qb->andWhere('e.organizer=:user');
            $qb->setParameter('user', $user);
        }

        //list the events the user has signed on
        if ($signedOn === 'on') {
            $qb->andWhere('p.id=:userId');
            $qb->setParameter('userId', $user->getId());
        }

        //list the events the user has NOT signed on
        if ($notSignedOn === 'on') {
            //get the events where user is participant
            $idEvents = $this->signedOnEvents($user->getId());
            //add where event.id is not in the array returned (id events)
            $qb->andWhere('e.id NOT IN (:idEvents)');
            $qb->setParameter('idEvents', $idEvents);
        }

        //list the events already happened
        if ($pastEvent === 'on') {
            $qb->andWhere('e.rdvTime<:today');
            $qb->setParameter('today', $today);
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * return every event the user has signed on
     * @param $idUser
     * @return array of the ids of the events
     */
    public function signedOnEvents($idUser)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.participants', 'p');
        $qb->andWhere('p.id = :idUser');
        $qb->setParameter('idUser', $idUser);
        $events = $qb->getQuery()->getResult();

        $idEvents = [];
        foreach ($events as $event) {
            $idEvents[] = $event->getId();
        }

        return $idEvents;
    }


    /**
     *
     * @param User $user
     * @param $idEvent
     * @return the event if the user is already signed on
     */
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


    /**
     * is used to get every event "open"
     */
    public function updateStateToClosed()
    {
        // get today's date
        $now = new \DateTime();
        // create a date of today plus one day
        $interval = \DateInterval::createFromDateString("1 day");
        $now->add($interval);
        $qb = $this->createQueryBuilder('e');
        // get every event which status is "ouvert" and which signon deadline is passed
        $qb->andWhere('e.state = :state')
            ->andWhere('e.signOnDeadline < :now')
            ->setParameters([
                'state' => 'ouvert',
                'now' => $now
            ]);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * is used to get every event "fermé"
     */
    public function updateStateToPassed()
    {
        // get today's date
        $now = new \DateTime();
        // create a date of today plus one day
        $interval = \DateInterval::createFromDateString("1 day");
        $now->add($interval);
        $qb = $this->createQueryBuilder('e');
        //get every event which status is "fermé" and which rdvTime is passed
        $qb->andWhere('e.state = :state')
            ->andWhere('e.rdvTime < :now')
            ->setParameters([
                'state' => 'fermé',
                'now' => $now
            ]);
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
