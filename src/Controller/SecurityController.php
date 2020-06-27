<?php

namespace App\Controller;


use App\Repository\JourneeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @var JourneeRepository
     */
    private $journeeRepository;

    /**
     * SecurityController constructor.
     * @param JourneeRepository $journeeRepository
     */
    public function __construct(JourneeRepository $journeeRepository)
    {
        $this->journeeRepository = $journeeRepository;
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function loginAction(AuthenticationUtils $utils)
    {
        $journees = $this->journeeRepository->findAll();
        if ($this->getUser() == null) {
            return $this->render('security/login.html.twig', [
                'journees' => $journees,
                'lastUsername' => $utils->getLastUsername(),
                'error' => $utils->getLastAuthenticationError()
            ]); // TODO Redirect to the good journee's page
        }
        else return $this->render('security/alreadyConnected.html.twig',[
            'journees' => $journees
        ]);
    }
}
