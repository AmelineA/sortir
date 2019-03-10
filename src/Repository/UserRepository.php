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

        $qb=$this->createQueryBuilder('u');
        $qb->andWhere('u.addedOn=:time');
        $qb->setParameter('time', $time);
        $query=$qb->getQuery();
        return $query->getResult();
    }


    /**
     * get all the users who want to be informed of new event on their site
     * @param Event $event
     * @return mixed
     */
    public function getUserBySiteAndBeInformed(Event $event)
    {
        $qb=$this->createQueryBuilder('u');
        $qb->andWhere('u.beInformed = true');
        $qb->andWhere('u.site = :organizerSite');
        $qb->setParameter('organizerSite', $event->getSite()->getId());
        $query = $qb->getQuery();
        return $query->getResult();
    }
//    public function findByAfterDate(DateTime $time)
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.addedOn=:time')
//            ->setParameter('time', $time)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
