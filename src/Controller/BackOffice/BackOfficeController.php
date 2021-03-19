<?php

namespace App\Controller\BackOffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{

    /**
     * @Route("/backoffice", name="backoffice")
     * @return Response
     */
    public function index(): Response
    {
        return $this->redirectToRoute('backoffice.rencontres');
    }
}
