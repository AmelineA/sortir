<?php

namespace App\Controller;


use App\Entity\Event;
use App\Entity\SearchEvent;
use App\Entity\Site;
use App\Form\SearchEventType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/accueil", name="home", methods={"GET", "POST"})
     *
     */
    public function home(Request $request)
    {

        $today=new \DateTime();
        $today->format("d-m-Y");
        $user=$this->getUser();
        $userSite=$this->getUser()->getSite();

    //    $signedOnEvents=$this->getUser()->getSignedOnEvents();

        $siteRepo=$this->getDoctrine()->getRepository(Site::class);

        $eventRepo=$this->getDoctrine()->getRepository(Event::class);



        $sites=$siteRepo->findAll();
        //dd($sites);           OK

        $site=$request->query->get('site');
        //dd($site);
        $searchBar=$request->query->get('searchBar');
        $dateStart=$request->query->get('dateStart');
        $dateEnd=$request->query->get('dateEnd');
        $organizer=$request->query->get('organizer');
        $signedOn=$request->query->get('signedOn');
        $notSignedOn=$request->query->get('notSignedOn');
        $pastEvents=$request->query->get('pastEvent');

        //dd($request->query);

//        if(!empty($request->query)){
//            $events=$eventRepo->findListEventsBy($user, $site, $searchBar, $dateStart, $dateEnd, $organizer, $signedOn, $notSignedOn, $pastEvents);
//            return $this->redirectToRoute('home', ['events'=>$events]);
//        }
        //    dd($request->get('dateStart'));
        //   $eventRepo->findListEventsBy($user, $site);


        $events=$eventRepo->listEventsBySite($user);


        return $this->render('app/home.html.twig', [
            'user'=>$user,
//            'userSite'=>$userSite,
            'events'=>$events,
//            'today'=>$today,
            'sites'=>$sites,


        ]);
    }





}
