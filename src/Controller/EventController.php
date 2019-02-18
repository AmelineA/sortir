<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{

    /**
     *
     * @IsGranted("ROLE_USER")
     * @Route(
     *     "/creer-une-sortie",
     *     name="create_event")
     */
    public function createEvent(Request $request)
    {
        $event=new Event();
        $event->setState("ouvert");
        $event->setOrganizer($this->getUser());
        $event->addParticipant($this->getUser());
        $event->setSite($this->getUser()->getSite());
        $eventForm=$this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', "La sortie a bien été créée !");

          return $this->redirectToRoute("home");
        }

        return $this->render('event/event-form.html.twig', [
            'eventForm'=>$eventForm->createView()
        ]);

    }


    /**
     * Sign on a user to an event if the user is connected,
     * if the inscriptions for this event are opened,
     * if user has not already signed on
     *
     * @IsGranted("ROLE_USER")
     * @Route(
     *     "/s'inscrire/{eventId}",
     *     name="sign_on_to_event")
     * @param $eventId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function signOnToEvent($eventId)
    {
        $em = $this->getDoctrine()->getManager();
        $eventRepo = $em->getRepository(Event::class);
        $event = $eventRepo->find($eventId);
        $nb=count($event->getParticipants());

        if(!empty($this->getUser()) && $nb < $event->getMaxNumber()){
            $user = $this->getUser();
            $alreadySignedOn = $eventRepo->alreadySignedOn($user, $eventId);
            if($event->getState()==='ouvert'){
                if(empty($alreadySignedOn)){
                    $event->addParticipant($this->getUser());
                    $em->persist($event);
                    $em->flush();
                }else{
                    $this->addFlash('danger', "Vous êtes déjà inscrit à cet évènement !");
                    return $this->redirectToRoute('home');
                }
            }else{
                $this->addFlash('danger', "Les inscriptions sont fermées pour cet évènement !");
                return $this->redirectToRoute('home');
            }
            $this->addFlash('success', "Merci, Vous êtes inscrit à la sortie !");
        }

        return $this->redirectToRoute('home');
    }


    /**
     *
     * @IsGranted("ROLE_USER")
     * @Route(
     *     "/se-désister/{eventId}",
     *     name="withdraw_event")
     * @param $eventId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function withdraw($eventId)
    {
        $em = $this->getDoctrine()->getManager();
        $eventRepo = $em->getRepository(Event::class);
        $event = $eventRepo->find($eventId);

        $now = new \DateTime();

        //check if the rdvTime is in the future
        if ($event->getRdvTime() > $now){
            $event->removeParticipant($this->getUser());
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', "Vous vous êtes désisté de la sortie !");
        }else{
            $this->addFlash('danger', "La sortie est passée!");
        }

        return $this->redirectToRoute('home');
    }


    /**
     *
     * @IsGranted("ROLE_USER")
     * @Route(
     *     "/modifier-une-sortie/{eventId}",
     *     name="modify_event",
     *     methods={"GET", "POST"}
     * )
     * @param $eventId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifiyEvent($eventId, Request $request)
    {
        $user=$this->getUser();
        $em = $this->getDoctrine()->getRepository(Event::class);
        $event = $em->find($eventId);
        $eventForm=$this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', "La sortie a bien été modifiée !");
            return $this->redirectToRoute("home");
        }

        return $this->render('event/modify-event.html.twig', [
            'user'=>$user,
            'event'=>$event,
            'eventForm'=>$eventForm->createView()
        ]);
    }

    /**
     *
     * @IsGranted("ROLE_USER")
     * @Route(
     *     "/annuler-une-sortie/{eventId}",
     *     name="cancel_event",
     *     methods={"GET", "POST"}
     * )
     * @param $eventId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancelEvent($eventId, Request $request)
    {
        $user=$this->getUser();
        $em = $this->getDoctrine()->getRepository(Event::class);
        $event = $em->find($eventId);

        if($_POST){

            $motif = $request->query->get('motif');
            $event->setDescription($motif);
            $event->setState('annulé');
            $em=$this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', "La sortie a bien été annulée !");
            return $this->redirectToRoute("home");
        }


        return $this->render('event/cancel-event.html.twig', [
            'user'=>$user,
            'event'=>$event
        ]);
    }

    /**
     *
     * @IsGranted("ROLE_USER")
     * @Route("/afficher-sortie/{eventId}", name="display_event", requirements={"id"="\d+"})
     */
    public function displayEvent($eventId)
    {
        $eventRepo=$this->getDoctrine()->getRepository(Event::class);
        $event=$eventRepo->find($eventId);
        $participants=$event->getParticipants();
        return $this->render('event/display-event.html.twig', [
            'event'=>$event,
            'participants'=>$participants
        ]);
    }

}
