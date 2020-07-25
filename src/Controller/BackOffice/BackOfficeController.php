<?php

namespace App\Controller\BackOffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{

    /**
     * @Route("/backoffice", name="back_office")
     * @return Response
     */
    public function index(){ return $this->redirectToRoute('back_office.competiteurs'); }
}
