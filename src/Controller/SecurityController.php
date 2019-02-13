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
     *     "/mon-profil/{id}",
     *     requirements={"id"="\d+"},
     *     name="app_register",
     *     methods={"GET", "POST"}
     *     )
     */
    public function register(int $id, UserPasswordEncoderInterface $encoder, Request $request )
    {
        //$user =new User();
        $em=$this->getDoctrine()->getRepository(User::class);
        $user = $em->find($id);

        $registerForm = $this->createForm(UserType::class, $user);
        $registerForm->handleRequest($request);

        if($registerForm->isSubmitted() && $registerForm->isValid()){

            $password = $user->getPassword();
            $hash = $encoder->encodePassword($user, $password);
            $user->setPassword($hash);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();


            $this->addFlash("success", 'Votre compte a bien été modifié ! ');
            return $this->redirectToRoute('home');
        }


        return $this->render('security/register.html.twig',[
          'registerForm'=>$registerForm->createView(),
          'user'=>$user
        ]);

    }

    /**
     * @Route("/profil/{id}", name="show_profile", requirements={"id"="\d+"})
     */
    public function showProfile($id)
    {
        $em=$this->getDoctrine()->getRepository(User::class);
        $user = $em->find($id);
        return $this->render('app/show-profile.html.twig', [
           'user'=>$user
        ]);
    }
}
