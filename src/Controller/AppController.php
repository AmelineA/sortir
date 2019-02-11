<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/accueil", name="home")
     */
    public function home()
    {
        return $this->render('app/home.html.twig');
    }
}
