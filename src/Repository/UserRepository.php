<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }


    public function findAfterDate($time)
    {

        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('u.addedOn=:time');
        $qb->setParameter('time', $time);
        $query = $qb->getQuery();
        return $query->getResult();
    }


    /**
     *  get all the users ordered by name
     * @return mixed
     */
    public function getUsersOrderByAsc()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->orderBy('u.firstName', 'ASC');
        $query = $qb->getQuery();
        return $query->getResult();
    }


    /**
     * get all the users who want to be informed of new event on their site
     * @param Event $event
     * @return mixed
     */
    public function getUserBySiteAndBeInformed(Event $event)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('u.beInformed = true');
        $qb->andWhere('u.site = :organizerSite');
        $qb->setParameter('organizerSite', $event->getSite()->getId());
        $query = $qb->getQuery();
        return $query->getResult();
    }

}
