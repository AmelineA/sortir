<?php

namespace App\Controller;

use App\Form\FirstConnectionType;
use App\Form\ResetType;
use App\Form\UserType;
use http\Message;
use App\Services\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{


    /**
     * @IsGranted("ROLE_USER")
     * automatic function by Symfony
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }


    /**
     * @Route("/", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
       /* $authChecker = $this->get('security.authorization_checker');
        $router = $this->get('router');*/

        //redirect to the home page if the user is already connected
        if ($this->isGranted("ROLE_USER")) {
            return $this->redirectToRoute('home');
        }

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

        if ($firstConnForm->isSubmitted() && $firstConnForm->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($currentUser);
            $em->flush();

            return $this->redirectToRoute('home');
        }
        return $this->render('security/first-connection.html.twig', [
            'firstConnForm' => $firstConnForm->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/mon-profil/{id}", name="app_my_profile", methods={"GET", "POST"})
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function updateMyProfile(Request $request)
    {
        $fileUploader = new FileUploader('img/profile-pictures');
        $currentUser = $this->getUser();
        $registerForm = $this->createForm(UserType::class, $currentUser);
        //gets the name of the picture file from the User object
        $profilePictureName = $currentUser->getProfilePictureName();
        //handleRequest gets then erases every field
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $currentUser = $registerForm->getData();
            $currentUser->setProfilePictureName($profilePictureName);

            //if the user wants to upload a picture
            if (null !== $registerForm->get("profilePictureName")->getData()) {
                //this block allows to add a picture

                //get the picture file
                $profilePicture = $registerForm->get("profilePictureName")->getData();
                //builds the unique filename with the real extension of the file and copies the file into the directory and deletes the former picture file
                $profilePictureName = $fileUploader->upload($profilePicture, $profilePictureName);
                // sets the file name in the User object
                $currentUser->setProfilePictureName($profilePictureName);
            } // or if the user does NOT want to upload, the link between the User object and the file name must be kept
            else {
                // sets again the name of the file to the User object
                $currentUser->setProfilePictureName($profilePictureName);
            }
          //persist in DB
            $em = $this->getDoctrine()->getManager();
            $em->persist($currentUser);
            $em->flush();

            $this->addFlash("success", 'Votre compte a bien été modifié ! ');
            return $this->redirectToRoute('home');
        }

        return $this->render('security/myprofile.html.twig', [
            'registerForm' => $registerForm->createView(),
            'user' => $currentUser
        ]);

    }

}