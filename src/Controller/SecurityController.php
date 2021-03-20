<?php

namespace App\Controller;

use App\Form\BackofficeCompetiteurAdminType;
use App\Form\CompetiteurType;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
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
    private $em;
    private $journeeDepartementaleRepository;
    private $journeeParisRepository;

    /**
     * SecurityController constructor.
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                EntityManagerInterface $em)
    {
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->em = $em;
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils): Response
    {
        if ($this->getUser() != null){
            return $this->redirectToRoute('index');
        } else {
            return $this->render('account/login.html.twig', [
                'lastUsername' => $utils->getLastUsername(),
                'error' => $utils->getLastAuthenticationError()
            ]);
        }
    }

    /**
     * @Route("/compte", name="account")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function edit(Request $request){
        $journees = [];
        if ($this->get('session')->get('type') == 'departementale') $journees = $this->journeeDepartementaleRepository->findAll();
        else if ($this->get('session')->get('type') == 'paris') $journees = $this->journeeParisRepository->findAll();

        $user = $this->getUser();

        if (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) $form = $this->createForm(BackofficeCompetiteurAdminType::class, $user);
        else $form = $this->createForm(CompetiteurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()){
                $this->em->flush();
                $this->addFlash('success', 'Informations modifiées !');
                return $this->redirectToRoute('account');
            }
            else {
                $this->addFlash('fail', 'Une erreur est survenue ...');
            }
        }

        return $this->render('account/edit.html.twig', [
            'type' => 'general',
            'user' => $user,
            'urlImage' => $user->getAvatar(),
            'path' => 'account.update.password',
            'journees' => $journees,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/update_password", name="account.update.password")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder){
        $journees = [];
        if ($this->get('session')->get('type') == 'departementale') $journees = $this->journeeDepartementaleRepository->findAll();
        else if ($this->get('session')->get('type') == 'paris') $journees = $this->journeeParisRepository->findAll();

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

        return $this->render('account/edit.html.twig', [
            'user' => $user,
            'type' => 'general',
            'urlImage' => $user->getAvatar(),
            'path' => 'account.update.password',
            'journees' => $journees,
            'form' => $formCompetiteur->createView()
        ]);
    }
}
