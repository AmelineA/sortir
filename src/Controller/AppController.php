<?php

namespace App\Controller;


use App\Entity\Event;
use App\Form\SearchEventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/accueil", name="home")
     */
    public function home(Request $request)
    {

        $today=new \DateTime();
        $today->format("d-m-Y");

        $user=$this->getUser();
        $site=$this->getUser()->getSite();

        $signedOnEvents=$this->getUser()->getSignedOnEvents();


        $eventRepo=$this->getDoctrine()->getRepository(Event::class);

        $events=$eventRepo->listEventsBySite($site);

        $searchEventForm=$this->createForm(SearchEventType::class, $events);
        $searchEventForm->handleRequest($request);

        if($searchEventForm->isSubmitted()){
            dd($request->get('dateStart'));
            $eventRepo->findListEventsBy($user, $site);
            return $this->redirectToRoute('home');
        }




        return $this->render('app/home.html.twig', [
            'user'=>$user,
            'site'=>$site,
            'events'=>$events,
            'today'=>$today,
            'searchEventForm'=>$searchEventForm->createView(),

        ]);
    }





}
