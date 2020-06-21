<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function loginAction(AuthenticationUtils $utils)
    {
        if ($this->getUser() == null) {
            return $this->render('security/login.html.twig', [
                'journee' => 'Index',
                'lastUsername' => $utils->getLastUsername(),
                'error' => $utils->getLastAuthenticationError()
            ]); // TODO Redirect to the good journee's page
        }
        else return $this->render('security/alreadyConnected.html.twig');
    }
}
