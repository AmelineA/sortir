<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserByAdminType;
use App\Form\UserByFileType;
use App\Form\UserType;
use App\Services\ConvertCsvToArray;
use App\Services\FileUploader;
use App\Services\UserImportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{


    /**
     * @Route(
     *     "/logout",
     *     name="app_logout")
     */
    public function logout(){}


    /**
     * @Route(
     *     "/",
     *     name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route(
     *     "/mon-profil",
     *     name="app_my_profile",
     *     methods={"GET", "POST"}
     *     )
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function updateMyProfile(UserPasswordEncoderInterface $encoder, Request $request, FileUploader $fileUploader )
    {
        $fileUploader=new FileUploader('%kernel.project_dir%/public/img/profile-pictures');
        $currentUser = $this->getUser();

        $registerForm = $this->createForm(UserType::class, $currentUser);
        $registerForm->handleRequest($request);

        if($registerForm->isSubmitted() && $registerForm->isValid()){

            $password = $currentUser->getPassword();
            $hash = $encoder->encodePassword($currentUser, $password);
            $currentUser->setPassword($hash);

            //Si le user veut uploader
            if(null!==$registerForm->get("profilePictureName")->getData()){
                //Permet d'ajouter une photo
                //TODO gérer la suppression des photos préexistantes. cf fichier pour uploader une photo, dans un des liens.
                //récupération du fichier photo
                $profilePicture = $registerForm->get("profilePictureName")->getData();
                //construction du nom de fichier unique avec l'extension réelle du fichier + copie du fichier dans le directory
                $profilePictureName=$fileUploader->upload($profilePicture);
                // attribution du nom de fichier dans l'objet User
                $currentUser->setProfilePictureName($profilePictureName);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($currentUser);
            $em->flush();

            $this->addFlash("success", 'Votre compte a bien été modifié ! ');
            return $this->redirectToRoute('home');
        }


        return $this->render('security/myprofile.html.twig',[
          'registerForm'=>$registerForm->createView(),
          'user'=>$currentUser
        ]);

    }

    /**
     * @Route("/profil/{id}",
     *     name="show_profile",
     *     requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showProfile($id)
    {
        $em=$this->getDoctrine()->getRepository(User::class);
        $user = $em->find($id);
        return $this->render('app/show-profile.html.twig', [
           'user'=>$user
        ]);
    }




    /**
     * allow a user registration manually by an admin
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
    public function registerUser(UserPasswordEncoderInterface $encoder, Request $request, \Swift_Mailer $swift_Mailer)
    {
        //form for user manually
        $user = new User();
        $registerForm = $this->createForm(UserByAdminType::class, $user);
        $registerForm->handleRequest($request);

        if($registerForm->isSubmitted() && $registerForm->isValid()){

            $year = new \DateTime();
            //generate a username with the first letter of the name, the firstname and the year of inscription
            $usernameFName = substr(strtolower($user->getFirstName()), 0, 1);
            $usernameName = strtolower($user->getName());
            $username = $usernameFName.$usernameName.$year->format("Y");
            //generate a password with the 2 first letters of the name, the first name and the year of inscription
            $passwordName = substr($user->getName(), 0, 2);
            $passwordFName = substr($user->getFirstName(), 0, 2);
            $password = $passwordName.$passwordFName.$year->format("Y");
            $hash = $encoder->encodePassword($user, $password);

            $user->setPassword($hash);
            $user->setUsername($username);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //send an email with informations to connect
            $message = new \Swift_Message();
            $message->setTo($user->getEmail())
                    ->setSubject("Votre inscription")
                    ->setFrom("ameline.aubin2018@campus-eni.fr")
                    ->setBody($this->renderView('email.html.twig', [
                        'username' => $username,
                        'password' => $password
                    ]));
            $swift_Mailer->send($message);

            $this->addFlash('success', "Un nouvel utilisateur a été inscrit");

            return $this->redirectToRoute('app_register');
        }

        return $this->render('security/register.html.twig', [
            'registerForm' => $registerForm->createView()
        ]);

    }


    /**
     * allow a csvimport by an admin
     * @IsGranted("ROLE_ADMIN")
     * @Route("/importer-fichier-csv",
     *     name="app_import_csv",
     *     methods={"GET", "POST"})
     */
    public function importCSVFile(Request $request, UserPasswordEncoderInterface $encoder, UserImportManager $userImportManager, ConvertCsvToArray $csvToArray)
    {
        $user= new User();
        $fileUploader=new FileUploader('%kernel.project_dir%/public/CSVusers');

        $csvForm=$this->createForm(UserByFileType::class, $user);
        $csvForm->handleRequest($request);

        if($csvForm->isSubmitted() && $csvForm->isValid()) {

            if(null!==$csvForm->get("csvFile")->getData()){

                //récupération du fichier
                $csvFile = $csvForm->get("csvFileName")->getData();
                //construction du nom de fichier unique avec l'extension réelle du fichier
                $csvFileName=$fileUploader->upload($csvFile);

                $usersArray=$csvToArray->convert($csvFileName, ',');

                $userImportManager->importUsers($usersArray, $encoder);

            }

            $this->addFlash("success", 'Votre compte a bien été modifié ! ');
            return $this->redirectToRoute('home');
        }
        return $this->render('security/register.html.twig', [
            'csvForm'=>$csvForm->createView()
        ]);
    }
}


