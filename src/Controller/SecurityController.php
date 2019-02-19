<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailResetType;
use App\Form\ResetType;
use App\Entity\CsvFile;
use App\Form\UserByAdminType;
use App\Form\UserByFileType;
use App\Form\UserType;
use http\Message;
use App\Services\ConvertCsvToArray;
use App\Services\FileUploader;
use App\Services\UserImportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\Session;


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
    public function updateMyProfile(UserPasswordEncoderInterface $encoder, Request $request )
    {
        $fileUploader=new FileUploader('img/profile-pictures');
        $currentUser = $this->getUser();
        $registerForm = $this->createForm(UserType::class, $currentUser);
        $profilePictureName = $currentUser->getProfilePictureName();
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
            }else{
                $currentUser->setProfilePictureName($profilePictureName);
            };
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

//    /**
//     * @Route("/profil/{id}",
//     *     name="show_profile",
//     *     requirements={"id"="\d+"})
//     * @param $id
//     * @return Response
//     */
//    public function showProfile($id)
//    {
//        $em=$this->getDoctrine()->getRepository(User::class);
//        $user = $em->find($id);
//        return $this->render('app/show-profile.html.twig', [
//            'user'=>$user
//        ]);
//    }




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
    public function registerUser(UserPasswordEncoderInterface $encoder, Request $request, \Swift_Mailer $swift_Mailer, UserImportManager $userImportManager, ConvertCsvToArray $csvToArray)
    {
        $user = new User();
        $csvFile= new CsvFile();
//die();    OK
        //form for user manually
        $registerForm = $this->createForm(UserByAdminType::class, $user);
        $registerForm->handleRequest($request);
//die();        OK
        //form for csv files
        $fileUploader=new FileUploader('CSVusers');
        $csvForm=$this->createForm(UserByFileType::class, $csvFile);
        $csvForm->handleRequest($request);
//die();
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

        if($csvForm->isSubmitted() && $csvForm->isValid()) {

            if(null!==$csvForm->get("csvFileName")->getData()){

                //getting the file
                $csvFile = $csvForm->get("csvFileName")->getData();
                //building a unique file name with the real file extension
                $csvFileName=$fileUploader->upload($csvFile);
//                dd($csvFileName);
                $csvPath=$fileUploader->getTargetDirectory()."/".$csvFileName;
//                dd($csvPath);
                //converting the file content to an object array of String
                $usersArray=$csvToArray->convert($csvPath, ',');
//                dd($usersArray);
                //importing the content of the array to the database
                $userImportManager->importUsers($usersArray, $encoder);
//                die();

            }

            $this->addFlash("success", 'Votre compte a bien été modifié ! ');
            return $this->redirectToRoute('home');
        }

        return $this->render('security/register.html.twig', [
            'registerForm' => $registerForm->createView(),
            'csvForm'=>$csvForm->createView()
        ]);

    }

    /**
     * @Route(
     *     "/mot-de-passe-oublie",
     *     name="reset_password",
     *     methods={"GET","POST"}
     *     )
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $resetForm = $this->createForm(EmailResetType::class);
        $resetForm->handleRequest($request);
        if ($resetForm->isSubmitted() && $resetForm->isValid()) {
            //si l'utilisateur renseigne le champ, on attribue un nouveau token comme password
            $emailField = $resetForm->getData()['email'];
            $user = $em->getRepository(User::class)->findOneByEmail($emailField);
            if ($user != null) {
                $token = uniqid();
                $user->setResetPassword($token);
                $em->persist($user);
                $em->flush();

                  //on envoie un email avec un lien dans lequel on passe le token
                  $mgClient = new \Swift_Message();
                  $mgClient->setTo('admin@fag.fr')//$user->getEmail()
                      ->setFrom('admin@fag.fr')
                      ->setSubject('demande de réilitialisation de mot de passe')
                      //crer la vue à enoyer et mettre le lien avec le token dedans
                      ->setBody($this->render('mail/token-email.html.twig', [
                          'token'=>$token
                      ]), 'text/html');

                  $mailer->send($mgClient);

                $this->addFlash('success', "Un email de réinitialisation vous a été envoyé.");
  //              return $this->render('mail/token-email.html.twig', [
  //                      'token'=>$token
  //                   ]);
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('security/reset-password.html.twig', [
            'resetForm' => $resetForm->createView()
        ]);
    }

    /**
     * @Route(
     *     "/token-email/{token}",
     *     name="token_email",
     *     methods={"GET","POST"}
     * )
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resetPasswordToken(Request $request, UserPasswordEncoderInterface $encoder, string $token)
    {
        //on compare le token reçu dans la requete avec celui stocké pour le user
        //$token = $request->query->get('token');
        if($_POST){

            if($token !== null){
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository(User::class)->findOneByResetPassword($token);

                //si le repo rammene qqch, on récurère la saisie et on modifie le password en bdd
                if ($user !== null){
                    $plainPassword = $request->request->get('password');
                    $hash = $encoder->encodePassword($user, $plainPassword);
                    $user->setPassword($hash);
                    $em->persist($user);
                    $em->flush();

                    //on redirige vers la page de login
                    $this->addFlash('successs', "votre mot de passe a bien été réinitialisé");
                    return $this->redirectToRoute('app_login');
                }
            }
        }
        return $this->render('security/reset-password-token.html.twig', [
            'token'=>$token
        ]);
    }
}