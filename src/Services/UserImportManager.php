<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 15/02/2019
 * Time: 16:04
 */

namespace App\Services;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserImportManager
{

    protected $em;

    /**
     * UserImportManager constructor.
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function importUsers($users, UserPasswordEncoderInterface $encoder)
    {

        foreach($users as $u){
            $user= new User();

            $year = new \DateTime();

            $user->setRoles($u["roles"]);
            $user->setName($u["name"]);
            $user->setFirstName($u["first_name"]);
            $user->setTelephone($u["telephone"]);
            $user->setEmail($u["email"]);
            $user->setActivated($u["activated"]);
            $user->setSite($u["site_id"]);
            $user->setProfilePictureName(null);


            //generate a username with the first letter of the name, the firstname and the year of inscription
            $usernameFName = substr(strtolower($user->getFirstName()), 0, 1);
            $usernameName = strtolower($user->getName());
            $username = $usernameFName.$usernameName.$year->format("Y");
            //generate a password with the 2 first letters of the name, the first name and the year of inscription
            $passwordName = substr($user->getName(), 0, 2);
            $passwordFName = substr($user->getFirstName(), 0, 2);
            $password = $passwordFName.$passwordName.$year->format("Y");
            $hash = $encoder->encodePassword($user, $password);

            $user->setPassword($hash);
            $user->setUsername($username);

            $this->em->persist($user);
        }

        $this->em->flush();

        return new Response('Users imported');
    }
}