<?php

namespace App\Controller;

use App\Entity\Moderation;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @IsGranted("ROLE_ADMIN")
 * Class AdminController
 * @package App\Controller
 */
class AdminController extends AbstractController
{

    /**
     * is used to list all the users
     * @IsGranted("ROLE_ADMIN")
     * @Route("/liste-utilisateurs", name="show_list_of_users")
     */
    public function showUsers()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->getUsersOrderByAsc();

        return $this->render('admin/list-of-users.html.twig', [
            'users' => $users
        ]);
    }


    /**
     * is used to list all the moderations
     * @IsGranted("ROLE_ADMIN")
     * @Route("/moderations", name="show_moderations")
     */
    public function showModerations()
    {
        $moderationRepo = $this->getDoctrine()->getRepository(Moderation::class);
        $moderations = $moderationRepo->findAll();

        return $this->render('admin/list-of-moderations.html.twig', [
            'moderations' => $moderations
        ]);
    }

    /**
     * @Route("profil/signale/{id}", name="show_profile_reported_user", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function showProfileReportedUser($id)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $userNb = count($userRepo->findAll());
        if ($id > 0 && $id <= $userNb) {
            $user = $userRepo->find($id);
            return $this->render('app/show-profile.html.twig', [
                'user' => $user,
                //moderation to true to display deactivated button
                'moderation' => true
            ]);
        } else {
            $this->addFlash('danger', "Utilisateur inconnu");
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/desactiver-utilisateur/{userId}", name="deactivate_user")
     */
    public function deactivateUser($userId)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->find($userId);
        $user->setActivated(false);
        $user->setRoles([]);
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('show_list_of_users');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/reactiver-utilisateur/{userId}", name="reactivate_user")
     */
    public function reactivateUser($userId)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->find($userId);
        $user->setActivated(true);
        $user->setRoles([]);
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('show_list_of_users');
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("supprimer-utilisateur/{userId}", name="delete_user")
     * @param $userId
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteUser($userId)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $targetUser = $userRepo->find($userId);


        if ($targetUser !== null) {
            $signedOnEvents = $targetUser->getSignedOnEvents();
            foreach ($signedOnEvents as $event) {
                $targetUser->removeSignedOnEvent($event);
            }
            $organizedEvents = $targetUser->getOrganizedEvents();
            foreach ($organizedEvents as $ev) {
                $ev->setOrganizer($this->getUser());
                $targetUser->removeOrganizedEvent($ev);
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove($targetUser);
            $em->flush();
            $this->addFlash('success', "L'utilisateur a bien été supprimé !");
        } else {
            return "can't find user";
        }
        return $this->redirectToRoute('show_list_of_users');
    }


    /**
     * is used to send messages to users when their profiles are created by an admin
     * @param \Swift_Mailer $mailer
     * @param \DateTime $time
     * @throws \Exception
     */
    private function sendMessageToUsers(\Swift_Mailer $mailer, \DateTime $time)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->findAfterDate($time);
        $year = new \DateTime();

        foreach ($users as $user) {
            $username = $user->getUsername();
            //generate a password with the 2 first letters of the name, the first name and the year of registration
            $passwordName = substr($user->getName(), 0, 2);
            $passwordFName = substr($user->getFirstName(), 0, 2);
            $password = $passwordFName . $passwordName . $year->format("Y");
            $message = new \Swift_Message();
            $message->setTo($user->getEmail())
                ->setSubject("Votre inscription")
                ->setFrom("ameline.aubin2018@campus-eni.fr")
                ->setBody($this->renderView('mail/email.html.twig', [
                    'username' => $username,
                    'password' => $password
                ]));
            $mailer->send($message);
        }
    }


}
