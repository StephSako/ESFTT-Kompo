<?php

namespace App\Controller;

use App\Entity\FirstPhase;
use App\Form\FirstPhaseType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\FirstPhaseRepository;
use App\Repository\JourneeRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Phase_1Controller
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @var FirstPhaseRepository
     */
    private $phase_1_Repository;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var CompetiteurRepository
     */
    private $competiteurRepository;
    /**
     * @var DisponibiliteRepository
     */
    private $disponibiliteRepository;
    /**
     * @var JourneeRepository
     */
    private $journeeRepository;

    /**
     * @param JourneeRepository $journeeRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param FirstPhaseRepository $phase_1_Repository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeRepository $journeeRepository, DisponibiliteRepository $disponibiliteRepository, CompetiteurRepository $competiteurRepository, FirstPhaseRepository $phase_1_Repository, EntityManagerInterface $em)
    {
        $this->phase_1_Repository = $phase_1_Repository;
        $this->em = $em;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->journeeRepository = $journeeRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('journee.show', [
            'id' => 1
        ]); //TODO Redirect à la prochaine journée
    }

    /**
     * @param FirstPhase $id
     * @return Response
     * @Route("/journee/{id}", name="journee.show")
     * @throws DBALException
     */
    public function journeeShow($id)
    {
        $joueursDeclares = $this->disponibiliteRepository->findAllDispos($id);
        $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id);
        $disposJoueur = $this->getUser() ? $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
        $compos = $this->phase_1_Repository->findBy(['idJournee' =>$id]);
        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC']);
        $journees = $this->journeeRepository->findAll();
        $journee = ($journees[$id - 1]);
        $selectedPlayers = $this->phase_1_Repository->getSelectedPlayers($compos);

        return $this->render('journee/show.html.twig', [
            'journee' => $journee,
            'journees' => $journees,
            'compos' => $compos,
            'selectedPlayers' => $selectedPlayers,
            'dispos' => $joueursDeclares,
            'joueursNonDeclares' => $joueursNonDeclares,
            'disposJoueur' => $disposJoueur,
            'competiteurs' => $competiteurs
        ]);
    }

    /**
     * @Route("/journee/edit/{id}", name="journee.edit")
     * @param FirstPhase $compo
     * @param Request $request
     * @return Response
     */
    public function edit(FirstPhase $compo, Request $request) : Response
    {
        $oldPlayers = $this->phase_1_Repository->findOneBy(['id' => $compo->getId()]);
        $j1 = $oldPlayers->getIdJoueur1();
        $j2 = $oldPlayers->getIdJoueur2();
        $j3 = $oldPlayers->getIdJoueur3();
        $j4 = $oldPlayers->getIdJoueur4();

        $form = $this->createForm(FirstPhaseType::class, $compo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** Décrémenter le brûlage des joueurs désélectionnés de la précédente compo **/
            if ($j1 != null) {
                $brulageOld1 = $j1->getBrulage();
                $brulageOld1[$compo->getIdEquipe()]--;
                $j1->setBrulage($brulageOld1);
            }

            if ($j2 != null) {
                $brulageOld2 = $j2->getBrulage();
                $brulageOld2[$compo->getIdEquipe()]--;
                $j2->setBrulage($brulageOld2);
            }

            if ($j3 != null) {
                $brulageOld3 = $j3->getBrulage();
                $brulageOld3[$compo->getIdEquipe()]--;
                $j3->setBrulage($brulageOld3);
            }

            if ($j4 != null) {
                $brulageOld4 = $j4->getBrulage();
                $brulageOld4[$compo->getIdEquipe()]--;
                $j4->setBrulage($brulageOld4);
            }

            /** Incrémenter le brûlage des joueurs sélectionnés de la nouvelle compo **/
            if ($form->getData()->getIdJoueur1() != null) {
                $brulage1 = $form->getData()->getIdJoueur1()->getBrulage();
                $brulage1[$compo->getIdEquipe()]++;
                $compo->getIdJoueur1()->setBrulage($brulage1);
            }

            if ($form->getData()->getIdJoueur2() != null) {
                $brulage2 = $form->getData()->getIdJoueur2()->getBrulage();
                $brulage2[$compo->getIdEquipe()]++;
                $compo->getIdJoueur2()->setBrulage($brulage2);
            }

            if ($form->getData()->getIdJoueur3() != null) {
                $brulage3 = $form->getData()->getIdJoueur3()->getBrulage();
                $brulage3[$compo->getIdEquipe()]++;
                $compo->getIdJoueur3()->setBrulage($brulage3);
            }

            if ($form->getData()->getIdJoueur4() != null) {
                $brulage4 = $form->getData()->getIdJoueur4()->getBrulage();
                $brulage4[$compo->getIdEquipe()]++;
                $compo->getIdJoueur4()->setBrulage($brulage4);
            }

            $this->em->flush();
            $this->addFlash('success', 'Composition modifiée avec succès !');

            return $this->redirectToRoute('journee.show', [
                'id' => $compo->getIdJournee()->getNJournee()
            ]);
        }

        $burntPlayers = $this->competiteurRepository->findBurnPlayers($compo->getIdEquipe());
        $almostBurntPlayers = $this->competiteurRepository->findAlmostBurnPlayers($compo->getIdEquipe());
        $journees = $this->journeeRepository->findAll();
        return $this->render('journee/edit.html.twig', [
            'burntPlayers' => $burntPlayers,
            'almostBurntPlayers' => $almostBurntPlayers,
            'journees' => $journees,
            'compo' => $compo,
            'form' => $form->createView()
        ]);
    }
}
