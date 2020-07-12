<?php

namespace App\Controller;

use App\Form\CompetiteurType;
use App\Repository\JourneeDepartementaleRepository;
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
     * @var JourneeDepartementaleRepository
     */
    private $journeeRepository;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * SecurityController constructor.
     * @param JourneeDepartementaleRepository $journeeRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeRepository, EntityManagerInterface $em)
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
            ]);
        }
        else return $this->render('security/alreadyConnected.html.twig',[
            'journees' => $journees
        ]);
    }

    /**
     * @Route("/compte", name="account")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function home(Request $request){
        $journees = $this->journeeRepository->findAll();
        $user = $this->getUser();
        // TODO View to modify competiteur's dispos

        $formCompetiteur = $this->createForm(CompetiteurType::class, $user);
        $formCompetiteur->handleRequest($request);

        if ($formCompetiteur->isSubmitted()) {
            if ($formCompetiteur->isValid()){
                $this->em->flush();
                $this->addFlash('success', 'Informations modifiées !');
            }
            else {
                $this->addFlash('fail', 'Une erreur est survenue ...');
            }
        }

        return $this->render('security/edit.html.twig', [
            'user' => $user,
            'journees' => $journees,
            'formCompetiteur' => $formCompetiteur->createView()
        ]);
    }

    /**
     * @Route("/compte/update_password", name="account.update.password")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder){
        $journees = $this->journeeRepository->findAll();
        $user = $this->getUser();

        $formCompetiteur = $this->createForm(CompetiteurType::class, $user);
        $formCompetiteur->handleRequest($request);

        if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
            $password = $encoder->encodePassword($user, $request->get('new_password'));
            $user->setPassword($password);

            $this->em->flush();
            $this->addFlash('success', 'Mot de passe modifié !');
        }
        else {
            $this->addFlash('fail', 'Les mots de passe ne correspond pas');
        }

        return $this->render('security/edit.html.twig', [
            'user' => $user,
            'journees' => $journees,
            'formCompetiteur' => $formCompetiteur->createView()
        ]);
    }
}
