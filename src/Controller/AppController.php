<?php

namespace App\Controller;


use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/accueil", name="home")
     */
    public function home()
    {
        $today=new \DateTime();
        $today->format("d-m-Y");
        $eventRepo=$this->getDoctrine()->getRepository(Event::class);
        $user=$this->getUser();
        $site=$this->getUser()->getSite();
        $events=$eventRepo->listEventsBySite($site);
        return $this->render('app/home.html.twig', [
            'user'=>$user,
            'site'=>$site,
            'events'=>$events,
            'today'=>$today,

        ]);
    }






//    /**
//     * @Route("/accueil", name="home")
//     *
//     */
//    public function listEvents()
////    {
////        $eventRepo=$this->getDoctrine()->getRepository(Event::class);
////        $events=$eventRepo->findAll();
////
////        return $this->render('app/home.html.twig', [
////            'events'=>$events,
////        ]);
////    }
}
