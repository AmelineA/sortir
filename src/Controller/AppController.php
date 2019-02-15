<?php

namespace App\Controller;


use App\Entity\Event;
use App\Entity\Site;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/accueil", name="home", methods={"GET"})
     * @throws \Exception
     */
    public function home()
    {
        $today=new \DateTime();
        $today->format("d-m-Y");
        $user=$this->getUser();
        $userSite=$this->getUser()->getSite();

        $siteRepo=$this->getDoctrine()->getRepository(Site::class);
        $sites=$siteRepo->findAll();

        $eventRepo=$this->getDoctrine()->getRepository(Event::class);

        $events=$eventRepo->listEventsBySite($user);

        return $this->render('app/home.html.twig', [
            'today'=>$today,
            'user'=>$user,
            'userSite'=>$userSite,
            'sites'=>$sites,
            'events'=>$events,
            'site'=>$site = "",
            'searchBar'=>$searchBar = "",
            'dateStart'=>$dateStart = "",
            'dateEnd'=>$dateEnd = "",
            'organizer'=>$organizer = "",
            'signedOn'=>$signedOn = "",
            'notSignedOn'=>$notSignedOn = "",
            'pastEvents'=>$pastEvents = "",
        ]);
    }


    /**
     * @IsGranted("ROLE_USER")
     * @Route("/recherche", name="search", methods="POST")
     * @throws \Exception
     */
    public function search(Request $request)
    {
        //éléments nécessaire à l'affichage de base de home
        $today=new \DateTime();
        $today->format("d-m-Y");

        $user=$this->getUser();

        $siteRepo=$this->getDoctrine()->getRepository(Site::class);
        $sites=$siteRepo->findAll();


        //récupération des données du formulaire
        $site=$request->request->get('site');
        $searchBar=$request->request->get('searchBar');
        $dateStart=$request->request->get('dateStart');
        $dateEnd=$request->request->get('dateEnd');
        $organizer=$request->request->get('organizer');
        $signedOn=$request->request->get('signedOn');
        $notSignedOn=$request->request->get('notSignedOn');
        $pastEvents=$request->request->get('pastEvents');



        $eventRepo=$this->getDoctrine()->getRepository(Event::class);

        //dd($site);
        $events=$eventRepo->findListEventsBy($user, $site, $searchBar, $dateStart, $dateEnd, $organizer, $signedOn, $notSignedOn, $pastEvents);
 //       dd($events);
        return $this->render('app/home.html.twig', [
            'today'=>$today,
            'user'=>$user,
            'sites'=>$sites,
            'events'=>$events,
            'site'=>$site,
            'searchBar'=>$searchBar,
            'dateStart'=>$dateStart,
            'dateEnd'=>$dateEnd,
            'organizer'=>$organizer,
            'signedOn'=>$signedOn,
            'notSignedOn'=>$notSignedOn,
            'pastEvents'=>$pastEvents

        ]);

    }


}
