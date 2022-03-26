<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Formation;
use App\Form\FormationType;
class RHController extends AbstractController
{
    /**
     * @Route("/RH", name="r_h")
     */
    public function index(): Response
    {
        echo "coucou";

        return $this->render('rh/index.html.twig', [
            'controller_name' => 'RHController',
        ]);
    }


}
