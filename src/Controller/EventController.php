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
        $event->setState("published");
        $event->setOrganizer($this->getUser());
        $event->setSite($this->getUser()->getSite());
        $eventForm=$this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', "La sortie a bien été créée !");

          //  return $this->redirectToRoute("event/display_event");
        }

        return $this->render('event/event-form.html.twig', [
            'eventForm'=>$eventForm->createView()
        ]);

    }


    /**
     * @Route("/s'inscrire/{idEvent}", name="sign_on_to_event")
     */
    public function signOnToEvent($idEvent)
    {
        $em = $this->getDoctrine()->getManager();
        $eventRepo = $em->getRepository(Event::class);
        $event = $eventRepo->find($idEvent);

        if(!empty($this->getUser())){
            if($event->getState()==='opened'){

            }
        }
        //if statut event is open

        //then signon


        return $this->redirectToRoute('home');
    }


    /**
     * @Route("/se-désister", name="withdraw_event")
     */
    public function withdraw()
    {



        return $this->redirectToRoute('home');
    }


}
