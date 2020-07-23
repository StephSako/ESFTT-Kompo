<?php

namespace App\Controller;

use App\Form\PhaseDepartementaleType;
use App\Form\PhaseParisBasType;
use App\Form\PhaseParisHautType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\PhaseDepartementaleRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\PhaseParisRepository;
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
     * @var PhaseDepartementaleRepository
     */
    private $phaseDepartementaleRepository;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var CompetiteurRepository
     */
    private $competiteurRepository;
    /**
     * @var DisponibiliteDepartementaleRepository
     */
    private $disponibiliteDepartementaleRepository;
    /**
     * @var DisponibiliteParisRepository
     */
    private $disponibiliteParisRepository;
    /**
     * @var JourneeDepartementaleRepository
     */
    private $journeeDepartementaleRepository;
    /**
     * @var JourneeParisRepository
     */
    private $journeeParisRepository;
    /**
     * @var PhaseParisRepository
     */
    private $phaseParisRepository;

    /**
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param PhaseDepartementaleRepository $phaseDepartementaleRepository
     * @param PhaseParisRepository $phaseParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                PhaseDepartementaleRepository $phaseDepartementaleRepository,
                                PhaseParisRepository $phaseParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->phaseDepartementaleRepository = $phaseDepartementaleRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->phaseParisRepository = $phaseParisRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        $dates = $this->journeeDepartementaleRepository->findAllDates();
        $NJournee = 0;

        while ($NJournee < 7 && (int) (new \DateTime())->diff($dates[$NJournee]["date"])->format('%R%a') <= 0){
            $NJournee++;
        }
        $NJournee++;

        return $this->redirectToRoute('journee.show', [
            'type' => 'departementale',
            'id' => $NJournee
        ]);
    }

    /**
     * @param $type
     * @param $id
     * @return Response
     * @throws DBALException
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journeeShow($type, $id)
    {
        $journee = $compos = $selectedPlayers = $joueursDeclares = $joueursNonDeclares = $journees = null;
        $disposJoueur = [];

        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC']);

        if ($type === 'departementale'){
            $disposJoueur = $this->getUser() ? $this->disponibiliteDepartementaleRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $journee = $this->journeeDepartementaleRepository->find($id);
            $joueursDeclares = $this->disponibiliteDepartementaleRepository->findAllDisposByJournee($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, 'disponibilite_departementale');
            $compos = $this->phaseDepartementaleRepository->findBy(['idJournee' => $id]);
            $selectedPlayers = $this->phaseDepartementaleRepository->getSelectedPlayers($compos);
            $journees = $this->journeeDepartementaleRepository->findAll();
        }
        else if ($type === 'paris'){
            $disposJoueur = $this->getUser() ? $this->disponibiliteParisRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $journee = $this->journeeParisRepository->find($id);
            $joueursDeclares = $this->disponibiliteParisRepository->findAllDisposByJournee($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, 'disponibilite_paris');
            $compos = $this->phaseParisRepository->findBy(['idJournee' => $id]);
            $selectedPlayers = $this->phaseParisRepository->getSelectedPlayers($compos);
            $journees = $this->journeeParisRepository->findAll();
        }

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
     * @Route("/composition/{type}/edit/{compo}", name="composition.edit")
     * @param string $type
     * @param $compo
     * @param Request $request
     * @return Response
     */
    public function edit($type, $compo, Request $request) : Response
    {
        $oldPlayers = $form = $j4 = $j5 = $j6 = $levelEquipe = $burntPlayers = $selectionnables = $almostBurntPlayers = $selectedPlayers = $journees = null;

        if ($type == 'departementale'){
            $compo = $this->phaseDepartementaleRepository->find($compo);
            $journeeSelectedPlayers = $this->phaseDepartementaleRepository->findBy(['idJournee' => $compo->getIdJournee()->getIdJournee()]);
            $selectionnables = $this->disponibiliteDepartementaleRepository->findSelectionnablesDepartementales($compo->getIdEquipe(), $compo->getIdJournee()->getIdJournee());
            $form = $this->createForm(PhaseDepartementaleType::class, $compo);
            $oldPlayers = $this->phaseDepartementaleRepository->findOneBy(['id' => $compo->getId()]);
            $j4 = $oldPlayers->getIdJoueur4();
            $selectedPlayers = $this->phaseDepartementaleRepository->getSelectedPlayers($journeeSelectedPlayers);
            $journees = $this->journeeDepartementaleRepository->findAll();
        }
        else if ($type == 'paris'){
            $compo = $this->phaseParisRepository->find($compo);
            $journeeSelectedPlayers = $this->phaseParisRepository->findBy(['idJournee' => $compo->getIdJournee()->getIdJournee()]);
            $selectionnables = $this->disponibiliteParisRepository->findSelectionnablesParis($compo->getIdEquipe(), $compo->getIdJournee()->getIdJournee());
            $levelEquipe = $compo->getIdEquipe()->getIdEquipe();
            $oldPlayers = $this->phaseParisRepository->findOneBy(['id' => $compo->getId()]);
            if ($levelEquipe === 1){
                $form = $this->createForm(PhaseParisHautType::class, $compo);
                $j4 = $oldPlayers->getIdJoueur4();
                $j5 = $oldPlayers->getIdJoueur5();
                $j6 = $oldPlayers->getIdJoueur6();
                $j7 = $oldPlayers->getIdJoueur7();
                $j8 = $oldPlayers->getIdJoueur8();
                $j9 = $oldPlayers->getIdJoueur9();
            }
            else if ($levelEquipe === 2){
                $form = $this->createForm(PhaseParisBasType::class, $compo);
            }
            $selectedPlayers = $this->phaseParisRepository->getSelectedPlayers($journeeSelectedPlayers);
            $journees = $this->journeeParisRepository->findAll();
        }

        $j1 = $oldPlayers->getIdJoueur1();
        $j2 = $oldPlayers->getIdJoueur2();
        $j3 = $oldPlayers->getIdJoueur3();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** Décrémenter le brûlage des joueurs désélectionnés de la précédente compo **/
            if ($j1 != null) {
                if ($type === 'departementale') {
                    $brulageOld1 = $j1->getBrulageDepartemental();
                    $brulageOld1[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j1->setBrulageDepartemental($brulageOld1);
                }
                else if ($type === 'paris') {
                    $brulageOld1 = $j1->getBrulageParis();
                    $brulageOld1[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j1->setBrulageParis($brulageOld1);
                }
            }

            if ($j2 != null) {
                if ($type === 'departementale') {
                    $brulageOld2 = $j2->getBrulageDepartemental();
                    $brulageOld2[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j2->setBrulageDepartemental($brulageOld2);
                }
                else if ($type === 'paris') {
                    $brulageOld2 = $j2->getBrulageParis();
                    $brulageOld2[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j2->setBrulageParis($brulageOld2);
                }
            }

            if ($j3 != null) {
                if ($type === 'departementale') {
                    $brulageOld3 = $j3->getBrulageDepartemental();
                    $brulageOld3[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j3->setBrulageDepartemental($brulageOld3);
                }
                else if ($type === 'paris') {
                    $brulageOld3 = $j3->getBrulageParis();
                    $brulageOld3[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j3->setBrulageParis($brulageOld3);
                }
            }

            if ($type === 'departementale' || ($type === 'paris' && $levelEquipe === 1)) {
                if ($j4 != null) {
                    if ($type === 'departementale') {
                        $brulageOld4 = $j4->getBrulageDepartemental();
                        $brulageOld4[$compo->getIdEquipe()->getIdEquipe()]--;
                        $j4->setBrulageDepartemental($brulageOld4);
                    }
                    else if ($type === 'paris'){
                        $brulageOld4 = $j4->getBrulageParis();
                        $brulageOld4[$compo->getIdEquipe()->getIdEquipe()]--;
                        $j4->setBrulageParis($brulageOld4);
                    }
                }
            }

            if ($type === 'paris' && $levelEquipe === 1) {
                if ($j5 != null) {
                    $brulageOld5 = $j5->getBrulageParis();
                    $brulageOld5[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j5->setBrulageParis($brulageOld5);
                }

                if ($j6 != null) {
                    $brulageOld6 = $j6->getBrulageParis();
                    $brulageOld6[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j6->setBrulageParis($brulageOld6);
                }

                if ($j7 != null) {
                    $brulageOld7 = $j7->getBrulageParis();
                    $brulageOld7[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j7->setBrulageParis($brulageOld7);
                }

                if ($j8 != null) {
                    $brulageOld8 = $j8->getBrulageParis();
                    $brulageOld8[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j8->setBrulageParis($brulageOld8);
                }

                if ($j9 != null) {
                    $brulageOld9 = $j9->getBrulageParis();
                    $brulageOld9[$compo->getIdEquipe()->getIdEquipe()]--;
                    $j9->setBrulageParis($brulageOld9);
                }
            }

            /** Incrémenter le brûlage des joueurs sélectionnés de la nouvelle compo **/
            if ($form->getData()->getIdJoueur1() != null) {
                if ($type === 'departementale') {
                    $brulage1 = $form->getData()->getIdJoueur1()->getBrulageDepartemental();
                    $brulage1[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur1()->setBrulageDepartemental($brulage1);
                }
                else if ($type === 'paris') {
                    $brulage1 = $form->getData()->getIdJoueur1()->getBrulageParis();
                    $brulage1[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur1()->setBrulageParis($brulage1);
                }
            }

            if ($form->getData()->getIdJoueur2() != null) {
                if ($type === 'departementale') {
                    $brulage2 = $form->getData()->getIdJoueur2()->getBrulageDepartemental();
                    $brulage2[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur2()->setBrulageDepartemental($brulage2);
                }
                else if ($type === 'paris') {
                    $brulage2 = $form->getData()->getIdJoueur2()->getBrulageParis();
                    $brulage2[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur2()->setBrulageParis($brulage2);
                }
            }

            if ($form->getData()->getIdJoueur3() != null) {
                if ($type === 'departementale') {
                    $brulage3 = $form->getData()->getIdJoueur3()->getBrulageDepartemental();
                    $brulage3[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur3()->setBrulageDepartemental($brulage3);
                }
                else if ($type === 'paris') {
                    $brulage3 = $form->getData()->getIdJoueur3()->getBrulageParis();
                    $brulage3[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur3()->setBrulageParis($brulage3);
                }
            }

            if ($type === 'departementale' || ($type === 'paris' && $levelEquipe === 1)) {
                if ($form->getData()->getIdJoueur4() != null) {
                    if ($type === 'departementale') {
                        $brulage4 = $form->getData()->getIdJoueur4()->getBrulageDepartemental();
                        $brulage4[$compo->getIdEquipe()->getIdEquipe()]++;
                        $compo->getIdJoueur4()->setBrulageDepartemental($brulage4);
                    }
                    else if ($type === 'paris'){
                        $brulage4 = $form->getData()->getIdJoueur4()->getBrulageParis();
                        $brulage4[$compo->getIdEquipe()->getIdEquipe()]++;
                        $compo->getIdJoueur4()->setBrulageParis($brulage4);
                    }
                }
            }

            if ($type === 'paris' && $levelEquipe === 1) {
                if ($form->getData()->getIdJoueur5() != null) {
                    $brulage5 = $form->getData()->getIdJoueur5()->getBrulageParis();
                    $brulage5[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur5()->setBrulageParis($brulage5);
                }

                if ($form->getData()->getIdJoueur6() != null) {
                    $brulage6 = $form->getData()->getIdJoueur6()->getBrulageParis();
                    $brulage6[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur6()->setBrulageParis($brulage6);
                }

                if ($form->getData()->getIdJoueur7() != null) {
                    $brulage7 = $form->getData()->getIdJoueur7()->getBrulageParis();
                    $brulage7[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur7()->setBrulageParis($brulage7);
                }

                if ($form->getData()->getIdJoueur8() != null) {
                    $brulage8 = $form->getData()->getIdJoueur8()->getBrulageParis();
                    $brulage8[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur8()->setBrulageParis($brulage8);
                }

                if ($form->getData()->getIdJoueur9() != null) {
                    $brulage9 = $form->getData()->getIdJoueur9()->getBrulageParis();
                    $brulage9[$compo->getIdEquipe()->getIdEquipe()]++;
                    $compo->getIdJoueur9()->setBrulageParis($brulage9);
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Composition modifiée avec succès !');

            return $this->redirectToRoute('journee.show', [
                'type' => $compo->getIdJournee()->getLinkType(),
                'id' => $compo->getIdJournee()->getIdJournee()
            ]);
        }

        if ($type === 'departementale'){
            $burntPlayers = $this->competiteurRepository->findBurnPlayersDepartementale($compo->getIdEquipe());
            $almostBurntPlayers = $this->competiteurRepository->findAlmostBurnPlayersDepartementale($compo->getIdEquipe());
        }
        else if ($type === 'paris'){
            $burntPlayers = $this->competiteurRepository->findBurnPlayersParis();
            $almostBurntPlayers = $this->competiteurRepository->findAlmostBurnPlayersParis();
        }

        return $this->render('journee/edit.html.twig', [
            'burntPlayers' => $burntPlayers,
            'selectionnables' => $selectionnables,
            'almostBurntPlayers' => $almostBurntPlayers,
            'selectedPlayers' => $selectedPlayers,
            'journees' => $journees,
            'compo' => $compo,
            'form' => $form->createView()
        ]);
    }
}
