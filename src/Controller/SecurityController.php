<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\EmailResetType;
use App\Form\FirstConnectionType;
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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{


    /**
     * @IsGranted("ROLE_USER")
     * automatic function by Symfony
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
        $authChecker=$this->get('security.authorization_checker');
        $router=$this->get('router');

        // gets the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/entrer-mes-infos", name="first_connection")
     */
    public function registerFirstConnection(Request $request)
    {
        $currentUser = $this->getUser();
        $firstConnForm = $this->createForm(FirstConnectionType::class, $currentUser);
        $firstConnForm->handleRequest($request);

        if($firstConnForm->isSubmitted() && $firstConnForm->isValid()){
//dd($currentUser);
            $em = $this->getDoctrine()->getManager();
            $em->persist($currentUser);
            $em->flush();

//            $this->addFlash("success", 'Votre compte a bien été modifié ! ');
            return $this->redirectToRoute('home');
        }
        return $this->render('security/first-connection.html.twig', [
            'firstConnForm' => $firstConnForm->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route(
     *     "/mon-profil/{id}",
     *     name="app_my_profile",
     *     methods={"GET", "POST"}
     *     )
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function updateMyProfile(UserPasswordEncoderInterface $encoder, Request $request, $id)
    {
        $fileUploader=new FileUploader('img/profile-pictures');
        $currentUser = $this->getUser();
        $registerForm = $this->createForm(UserType::class, $currentUser);
        //gets the name of the picture file from the User object
        $profilePictureName=$currentUser->getProfilePictureName();
        // gets the password to keep the connection
        $password = $this->getUser()->getPassword();
        //handleRequest gets then erases every field
        $registerForm->handleRequest($request);

        if($registerForm->isSubmitted() && $registerForm->isValid()){
            $currentUser = $registerForm->getData();
            $currentUser->setProfilePictureName($profilePictureName);

                //if the user wants to upload a picture
                if (null !== $registerForm->get("profilePictureName")->getData()) {
                    //this block allows to add a picture

                    //get the picture file
                    $profilePicture = $registerForm->get("profilePictureName")->getData();
                    //builds the unique filename with the real extension of the file and copies the file into the directory
                    $profilePictureName = $fileUploader->upload($profilePicture);
                    // sets the file name in the User object
                    $currentUser->setProfilePictureName($profilePictureName);
                } // or if the user does NOT want to upload, the link between the User object and the file name must be kept
                else {
                    // sets again the name of the file to the User object
                    $currentUser->setProfilePictureName($profilePictureName);
                }

                //si le champs pseudo est vide, inserer firstname.name
                $fieldUserName = $registerForm->get('username')->getData();
                if($fieldUserName === ""){
                        $currentUser->setUserName($currentUser->getFirstName() . "." . $currentUser->getName());
                }

                //persiter en BDD
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


//    AUTH
//    /**
//     * @IsGranted("ROLE_USER")
//     * @Route(
//     *     "/changer-de-mot-de-passe",
//     *     name="change_password",
//     *     methods={"GET", "POST"}
//     *     )
//     */
//    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder)
//    {
//        $user = $this->getUser();
//        $changePasswordForm = $this->createForm(ChangePasswordType::class);
//        $changePasswordForm->handleRequest($request);
//
//        if($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()){
//
//            if($encoder->isPasswordValid($user, $changePasswordForm->get('oldPassword')->getData())){
//                $newPassword = $changePasswordForm->get('newPassword')->getData();
//                $hash = $encoder->encodePassword($user, $newPassword);
//                $user->setPassword($hash);
//                $em = $this->getDoctrine()->getManager();
//                $em->persist($user);
//                $em->flush();
//
//                $this->addFlash('success', 'Votre mot de passe a bien été modifié');
//                return $this->redirectToRoute('app_login');
//            }else{
//                $this->addFlash('warning', 'Identifiants incorrects');
//            }
//
//        }
//
//        return $this->render('security/change-password.html.twig',[
//            'changePasswordForm'=>$changePasswordForm->createView(),
//            'user'=>$user
//        ]);
//
//
//    }
//
//    /**
//     * is used when a user asks to reset his/her password when forgotten
//     * @Route(
//     *     "/mot-de-passe-oublie",
//     *     name="reset_password",
//     *     methods={"GET","POST"}
//     *     )
//     * @param Request $request
//     * @return Response
//     */
//    public function resetPassword(Request $request, \Swift_Mailer $mailer)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $resetForm = $this->createForm(EmailResetType::class);
//        $resetForm->handleRequest($request);
//        if ($resetForm->isSubmitted() && $resetForm->isValid()) {
//            // if the user types his/her email in the field, a new token is set in the reset_password column in database
//            $emailField = $resetForm->getData()['email'];
//            $user = $em->getRepository(User::class)->findOneByEmail($emailField);
//            if ($user != null) {
//                $token = uniqid();
//                //resetPassword attribute in User object is mapped to the reset_password column in database
//                $user->setResetPassword($token);
//                $em->persist($user);
//                $em->flush();
//
//                // an email including a link with the token in parameter  is sent
//                $mgClient = new \Swift_Message();
//                $mgClient->setTo($user->getEmail())
//                    ->setFrom('admin@fag.fr')
//                    ->setSubject('demande de réinitialisation de mot de passe')
//                    // creates the view to be sent and includes the link containing the token
//                    ->setBody($this->render('mail/token-email.html.twig', [
//                        'token'=>$token
//                    ]), 'text/html');
//                $mailer->send($mgClient);
//
//                $this->addFlash('success', "Un email de réinitialisation vous a été envoyé.");
//
//                return $this->redirectToRoute('app_login');
//            }
//            else{
//                $this->addFlash('warning', "Votre email est inconnu. Etes-vous sûr(e) de l'avoir saisi correctement?");
//                return $this->redirectToRoute('app_login');
//            }
//        }
//        return $this->render('security/reset-password.html.twig', [
//            'resetForm' => $resetForm->createView()
//        ]);
//    }
//
//    /**
//     * is the link included in the email sent to display the form to let the user reset the password
//     * @Route(
//     *     "/token-email/{token}",
//     *     name="token_email",
//     *     methods={"GET","POST"}
//     * )
//     * @param Request $request
//     * @param UserPasswordEncoderInterface $encoder
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
//     */
//    public function resetPasswordToken(Request $request, UserPasswordEncoderInterface $encoder, string $token)
//    {
//        //on compare le token reçu dans la requete avec celui stocké pour le user
//        //$token = $request->query->get('token');
//        if($_POST){
//
//            if($token !== null){
//                $em = $this->getDoctrine()->getManager();
//                $user = $em->getRepository(User::class)->findOneByResetPassword($token);
//
//                //si le repo rammene qqch, on récurère la saisie et on modifie le password en bdd
//                if ($user !== null){
//                    $plainPassword = $request->request->get('password');
//                    $hash = $encoder->encodePassword($user, $plainPassword);
//                    $user->setPassword($hash);
//                    $em->persist($user);
//                    $em->flush();
//
//                    //on redirige vers la page de login
//                    $this->addFlash('successs', "Votre mot de passe a bien été réinitialisé");
//                    return $this->redirectToRoute('app_login');
//                }
//            }
//        }
//        return $this->render('security/reset-password-token.html.twig', [
//            'token'=>$token
//        ]);
//    }
}