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
        /*if ($this->getUser() == null) {
            return $this->render('pages/login.twig', [
                'lastusername' => $utils->getLastUsername(),
                'error' => $utils->getLastAuthenticationError(),
            ]);
        }
        else return $this->render('redirect/connected.html.twig', [
            'categories' => $this->categories
        ]);*/ //TODO Already connected

        return $this->render('security/login.html.twig', [
            'journee' => 'Index',
            'lastUsername' => $utils->getLastUsername(),
            'error' => $utils->getLastAuthenticationError()
        ]);
    }
}
