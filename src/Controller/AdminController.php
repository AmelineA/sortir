<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\CsvFile;
use App\Form\UserByAdminType;
use App\Form\UserByFileType;
use App\Services\ConvertCsvToArray;
use App\Services\FileUploader;
use App\Services\UserImportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @IsGranted("ROLE_ADMIN")
 * Class AdminController
 * @package App\Controller
 */
class AdminController extends AbstractController
{
    /**
     * allow a user registration manually by an admin
     * allow a csvimport by an admin
     * @IsGranted("ROLE_ADMIN")
     * @Route("/inscrire-utilisateur",
     *     name="app_register",
     *     methods={"GET", "POST"})
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @param \Swift_Mailer $swift_Mailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function registerUser(UserPasswordEncoderInterface $encoder, Request $request, \Swift_Mailer $mailer, UserImportManager $userImportManager, ConvertCsvToArray $csvToArray)
    {
        $user = new User();
        $csvFile = new CsvFile();
        $time = new \DateTime();

        //form for user manually
        $registerForm = $this->createForm(UserByAdminType::class, $user);
        $registerForm->handleRequest($request);

        //form for csv files
        $fileUploader = new FileUploader('CSVusers');
        $csvForm = $this->createForm(UserByFileType::class, $csvFile);
        $csvForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {

            $year = new \DateTime();
            //generate a username with the first letter of the name, the firstname and the year of registration
            $usernameFName = substr(strtolower($user->getFirstName()), 0, 1);
            $usernameName = strtolower($user->getName());
            $username = $usernameFName . $usernameName . $year->format("Y");
            //generate a password with the 2 first letters of the name, the first name and the year of registration
            $passwordName = substr($user->getName(), 0, 2);
            $passwordFName = substr($user->getFirstName(), 0, 2);
            $password = $passwordName . $passwordFName . $year->format("Y");
            $hash = $encoder->encodePassword($user, $password);

            $user->setPassword($hash);
            $user->setUsername($username);
            $user->setAddedOn(new \DateTime('now'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //send an email with information to connect
            $message = new \Swift_Message();
            $message->setTo($user->getEmail())
                ->setSubject("Votre inscription")
                ->setFrom("ameline.aubin2018@campus-eni.fr")
                ->setBody($this->renderView('mail/email.html.twig', [
                    'username' => $username,
                    'password' => $password
                ]));
            $mailer->send($message);

            $this->addFlash('success', "Un nouvel utilisateur a été inscrit");

            return $this->redirectToRoute('app_register');
        }

        if ($csvForm->isSubmitted() && $csvForm->isValid()) {

            if (null !== $csvForm->get("csvFileName")->getData()) {

                //getting the file
                $csvFile = $csvForm->get("csvFileName")->getData();
                //building a unique file name with the real file extension
                $csvFileName = $fileUploader->upload($csvFile);
                $csvPath = $fileUploader->getTargetDirectory() . "/" . $csvFileName;

                //converting the file content to an object array of String
                $usersArray = $csvToArray->convert($csvPath, ',');
                //importing the content of the array to the database
                $time=$userImportManager->importUsers($usersArray, $encoder);

                $this->sendMessageToUsers($mailer, $time);
            }
            $this->addFlash("success", 'Les utilisateurs ont bien été créés ! ');
            return $this->redirectToRoute('home');
        }

        return $this->render('security/register.html.twig', [
            'registerForm' => $registerForm->createView(),
            'csvForm' => $csvForm->createView()
        ]);

    }


    /**
     * is used to list all the users
     * @IsGranted("ROLE_ADMIN")
     * @Route("/liste-utilisateurs", name="show_list_of_users")
     */
    public function showUsers()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->findAll();

        return $this->render('admin/list-of-users.html.twig', [
            'users' => $users
        ]);
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
            $signedOnEvents=$targetUser->getSignedOnEvents();
            foreach($signedOnEvents as $event){
                $targetUser->removeSignedOnEvent($event);
            }
            $organizedEvents=$targetUser->getOrganizedEvents();
            foreach ($organizedEvents as $ev){
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
        $userRepo=$this->getDoctrine()->getRepository(User::class);
        $users=$userRepo->findAfterDate($time);
//        dd($users)        //OK
        $year = new \DateTime();

        foreach($users as $user){
            $username=$user->getUsername();
            //generate a password with the 2 first letters of the name, the first name and the year of registration
            $passwordName = substr($user->getName(), 0, 2);
            $passwordFName = substr($user->getFirstName(), 0, 2);
            $password = $passwordFName.$passwordName.$year->format("Y");
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
//
    }


}
