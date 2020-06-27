<?php

namespace App\Controller;


use App\Form\CompetiteurType;
use App\Repository\JourneeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @var JourneeRepository
     */
    private $journeeRepository;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * SecurityController constructor.
     * @param JourneeRepository $journeeRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeRepository $journeeRepository, EntityManagerInterface $em)
    {
        $this->journeeRepository = $journeeRepository;
        $this->em = $em;
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

    /**
     * @Route("/compte", name="account")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function home(Request $request, UserPasswordEncoderInterface $encoder){
        $journees = $this->journeeRepository->findAll();
        $user = $this->getUser();
        // TODO See user's dispos

        $form = $this->createForm(CompetiteurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*$password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);*/ //TODO Update password

            $this->em->flush();
            $this->addFlash('success', 'Informations modifiées avec succès');
            return $this->redirect($request->getUri());
        }

        return $this->render('security/edit.html.twig', [
            'user' => $user,
            'journees' => $journees,
            'form' => $form->createView()
        ]);
    }
}
