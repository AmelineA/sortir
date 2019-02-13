<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{


    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(){}


    /**
     * @Route("/", name="app_login")
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
     *     name="app_register",
     *     methods={"GET", "POST"}
     *     )
     */
    public function register(UserPasswordEncoderInterface $encoder, Request $request )
    {
        $currentUser = $this->getUser();

        $registerForm = $this->createForm(UserType::class, $currentUser);
        $registerForm->handleRequest($request);

        if($registerForm->isSubmitted() && $registerForm->isValid()){

            $password = $currentUser->getPassword();
            $hash = $encoder->encodePassword($currentUser, $password);
            $currentUser->setPassword($hash);

            $em = $this->getDoctrine()->getManager();
            $em->persist($currentUser);
            $em->flush();


            $this->addFlash("success", 'Votre compte a bien été modifié ! ');
            return $this->redirectToRoute('home');
        }


        return $this->render('security/register.html.twig',[
          'registerForm'=>$registerForm->createView(),
          'user'=>$currentUser
        ]);

    }

}
