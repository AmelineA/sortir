<?php

namespace App\Repository;

use App\Entity\Moderation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Moderation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Moderation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Moderation[]    findAll()
 * @method Moderation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModerationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Moderation::class);
    }

    /**
     *  get all the moderations ordered by date
     * @return Moderation[]
     */
    public function getModerationsOrderByDateDesc()
    {
        $qb = $this->createQueryBuilder('m');
        $qb->orderBy('m.date', 'DESC');
        $query = $qb->getQuery();
        return $query->getResult();
    }
  }
