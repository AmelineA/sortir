<?php

namespace App\Controller;


use App\Entity\Event;
use App\Entity\Moderation;
use App\Entity\Site;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @isGranted("ROLE_USER")
 * Class AppController
 * @package App\Controller
 */
class AppController extends AbstractController
{
    /**
     * is used to display the home page with a list of events depending of the user's site
     * @IsGranted("ROLE_USER")
     * @Route("/accueil", name="home", methods={"GET", "POST"})
     * @throws \Exception
     */
    public function home()
    {
        if (empty($this->getUser()->getSite())) {
            return $this->redirectToRoute('first_connection'); //rediriger vers le formulaire de choix du site
        }

        $today = new \DateTime();

        $user = $this->getUser();
        $userSite = $this->getUser()->getSite();

        $siteRepo = $this->getDoctrine()->getRepository(Site::class);
        $sites = $siteRepo->findAll();

        $eventRepo = $this->getDoctrine()->getRepository(Event::class);

        $events = $eventRepo->listEventsBySite($user);

        return $this->render('app/home.html.twig', [
            'today' => $today,
            'user' => $user,
            'userSite' => $userSite,
            'sites' => $sites,
            'events' => $events,
            'site' => $site = "",
            'searchBar' => $searchBar = "",
            'dateStart' => $dateStart = "",
            'dateEnd' => $dateEnd = "",
            'organizer' => $organizer = "",
            'signedOn' => $signedOn = "",
            'notSignedOn' => $notSignedOn = "",
            'pastEvents' => $pastEvents = "",
        ]);
    }


    /**
     * is used to search events depending on criterias selected by the user
     * @IsGranted("ROLE_USER")
     * @Route("/recherche", name="search", methods="POST")
     * @throws \Exception
     */
    public function search(Request $request)
    {
        //éléments nécessaire à l'affichage de base de home
        $today = new \DateTime();

        $user = $this->getUser();

        $siteRepo = $this->getDoctrine()->getRepository(Site::class);
        $sites = $siteRepo->findAll();


        //récupération des données du formulaire
        $site = $request->request->get('site');
        $searchBar = $request->request->get('searchBar');
        $dateStart = $request->request->get('dateStart');
        $dateEnd = $request->request->get('dateEnd');
        $organizer = $request->request->get('organizer');
        $signedOn = $request->request->get('signedOn');
        $notSignedOn = $request->request->get('notSignedOn');
        $pastEvents = $request->request->get('pastEvents');

        $eventRepo = $this->getDoctrine()->getRepository(Event::class);

        //dd($site);
        $events = $eventRepo->findListEventsBy($user, $site, $searchBar, $dateStart, $dateEnd, $organizer, $signedOn, $notSignedOn, $pastEvents);

        return $this->render('app/home.html.twig', [
            'today' => $today,
            'user' => $user,
            'sites' => $sites,
            'events' => $events,
            'site' => $site,
            'searchBar' => $searchBar,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'organizer' => $organizer,
            'signedOn' => $signedOn,
            'notSignedOn' => $notSignedOn,
            'pastEvents' => $pastEvents

        ]);

    }


    /**
     * is used to show other users'profiles
     * @Route("/profil/{id}", name="show_profile", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showProfile($id)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $userNb = count($userRepo->findAll());
        if ($id > 0 && $id <= $userNb) {
            $user = $userRepo->find($id);
            return $this->render('app/show-profile.html.twig', [
                'user' => $user,
                //moderation to false to display the moderation button
                'moderation' => false
            ]);
        } else {
            $this->addFlash('danger', "Utilisateur inconnu");
            return $this->redirectToRoute('home');
        }
    }

    /**
     * is used to moderate a content
     * @Route("/moderation/{id}", name="moderate", methods="GET", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moderate(Request $request, $id)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $reporter = $this->getUser();

        $moderation = new Moderation();
        $moderation->setReportedUserId($id);
        $moderation->setReporterId($reporter->getId());
        $moderation->setStatus("en attente de modération");
        $moderation->setReporterName($reporter->getUsername());
        $moderation->setReportedUserName($userRepo->find($id)->getUsername());

        try {
            $moderation->setDate(new \DateTime());
        } catch (\Exception $e) {
            //TODO catch the exception properly
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($moderation);
        $em->flush();


        $this->addFlash('danger', "Utilisateur signalé. L'équipe du BDE commence son enquête et prendra les mesures nécessaires si besoin");
        return $this->showProfile($id);
    }


}
