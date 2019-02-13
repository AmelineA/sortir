<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 12/02/2019
 * Time: 09:28
 */

namespace App\Services;


use App\Entity\Event;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class EventService
{

    protected $em;

    /**
     * EventService constructor.
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }







}