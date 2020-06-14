<?php

namespace App\Controller;

use App\Repository\Phase_1Repository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Phase_1Controller
 * @package App\Controller
 */
class HomeController extends AbstractController
{

    /**
     * @var Phase_1Repository
     */
    private $phase_1_Repository;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param Phase_1Repository $phase_1_Repository
     * @param EntityManagerInterface $em
     */
    public function __construct(Phase_1Repository $phase_1_Repository, EntityManagerInterface $em)
    {
        $this->phase_1_Repository = $phase_1_Repository;
        $this->em = $em;
    }

    /**
     * @Route("/", name="index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('journee/show.html.twig', [
            'journee' => 'Index'
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @Route("/journee/{id}", name="journee.show")
     */
    public function journeeShow($id)
    {
        $journee = $this->phase_1_Repository->findBy(['journee' => $id]);
        return $this->render('journee/show.html.twig', [
            'journee' => $journee
        ]);
    }
}
