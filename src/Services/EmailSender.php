<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 19/02/2019
 * Time: 14:39
 */

namespace App\Services;


use App\Entity\User;

class EmailSender
{
    //send an email with information to connect
    public function sendEmail(\Swift_Mailer $swift_Mailer, User $currentUser, $controller)
    {
        $message = new \Swift_Message();
        $message->setTo($currentUser->getEmail())
            ->setSubject("Votre inscription")
            ->setFrom("ameline.aubin2018@campus-eni.fr")
            ->setBody($controller->renderView('email.html.twig', [
                'username' => $currentUser->getUsername(),
                'password' => $currentUser->getPassword(),
            ]));
        $swift_Mailer->send($message);
    }
}