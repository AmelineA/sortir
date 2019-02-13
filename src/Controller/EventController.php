<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{

    /**
     * @Route("/creer-une-sortie", name="create_event")
     */
    public function createEvent(Request $request)
    {
        $event=new Event();
        $event->setState("ouvert");
        $event->setOrganizer($this->getUser());
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
     * if user is not already signed on
     * @Route("/s'inscrire/{idEvent}", name="sign_on_to_event")
     * @param $idEvent
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function signOnToEvent($idEvent)
    {
        $em = $this->getDoctrine()->getManager();
        $eventRepo = $em->getRepository(Event::class);
        $event = $eventRepo->find($idEvent);

        $nb=count($event->getParticipants());

        //dd($event->getMaxNumber());
        if(!empty($this->getUser()) && $nb < $event->getMaxNumber()){
            $user = $this->getUser();
            $alreadySignedOn = $eventRepo->alreadySignedOn($user, $idEvent);
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
     * @Route("/se-désinscrire/{idEvent}", name="withdraw_event")
     * @param $idEvent
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function withdraw($idEvent)
    {
        $em = $this->getDoctrine()->getManager();
        $eventRepo = $em->getRepository(Event::class);
        $event = $eventRepo->find($idEvent);

        $now = new \DateTime();

        //check if the rdvTime is in the future
        if ($event->getRdvTime() > $now){

            $event->removeParticipant($this->getUser());
            $em->persist($event);
            $em->flush();
            //dd($event->getParticipants());
            $this->addFlash('success', "Vous vous êtes désisté de la sortie !");

       // }

        }else{
            $this->addFlash('danger', "La sortie est passée!");
        }


        return $this->redirectToRoute('home');
    }


    /**
     * @Route(
     *     "/modifier-une-sortie/{idEvent}",
     *     name="modify-event",
     *     methods={"GET", "POST"}
     * )
     * @param $idEvent
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifiyEvent($idEvent, Request $request)
    {
        $user=$this->getUser();
        $em = $this->getDoctrine()->getRepository(Event::class);
        $event = $em->find($idEvent);
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
     * @Route(
     *     "/annuler-une-sortie/{idEvent}",
     *     name="cancel-event",
     *     methods={"GET", "POST"}
     * )
     * @param $idEvent
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancelEvent($idEvent, Request $request)
    {
        $user=$this->getUser();
        $em = $this->getDoctrine()->getRepository(Event::class);
        $event = $em->find($idEvent);

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
}
