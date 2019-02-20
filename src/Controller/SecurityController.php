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
        //récupération du nom de la photo présent dans l'objet User
        $profilePictureName=$currentUser->getProfilePictureName();
        //handleReques récupère puis efface tous les champs
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
            //ou si le user ne veut pas uploader, il faut conserver le lien entre le User et le nom de fichier
            else{
                //réattribution du nom de la photo à l'objet User
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
                  $mgClient->setTo($user->getEmail())
                      ->setFrom('admin@fag.fr')
                      ->setSubject('demande de réinitialisation de mot de passe')
                      //créer la vue à envoyer et mettre le lien avec le token dedans
                      ->setBody($this->render('mail/token-email.html.twig', [
                          'token'=>$token
                      ]), 'text/html');
                  $mailer->send($mgClient);

                $this->addFlash('success', "Un email de réinitialisation vous a été envoyé.");

                return $this->redirectToRoute('app_login');
            }
            else{
                $this->addFlash('warning', "Votre email est inconnu au bataillon");
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