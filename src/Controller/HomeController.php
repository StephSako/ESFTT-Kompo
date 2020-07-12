<?php

namespace App\Controller;

use App\Entity\PhaseDepartementale;
use App\Form\PhaseDepartementaleType;
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
        return $this->redirectToRoute('journee.show', [
            'type' => 'departementale',
            'id' => 1
        ]);
    }

    /**
     * @param $type
     * @param PhaseDepartementale $id
     * @return Response
     * @throws DBALException
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journeeShow($type, $id) //TODO Correct journée(s) entre paris et departemental
    {
        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC']);

        $journeesDepartementales = $this->journeeDepartementaleRepository->findAll();
        $journeesParis = $this->journeeParisRepository->findAll();

        if ($type === 'departementale'){
            $disposJoueur = $this->getUser() ? $this->disponibiliteDepartementaleRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $journee = $this->journeeDepartementaleRepository->find($id);
            $joueursDeclares = $this->disponibiliteDepartementaleRepository->findAllDispos($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, 'disponibilite_departementale');
            $compos = $this->phaseDepartementaleRepository->findBy(['idJournee' =>$id]);
            $selectedPlayers = $this->phaseDepartementaleRepository->getSelectedPlayers($compos);
        }
        else if ($type === 'paris'){
            $disposJoueur = $this->getUser() ? $this->disponibiliteParisRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $journee = $this->journeeParisRepository->find($id);
            $joueursDeclares = $this->disponibiliteParisRepository->findAllDispos($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, 'disponibilite_paris');
            $compos = $this->phaseParisRepository->findBy(['idJournee' =>$id]);
            $selectedPlayers = $this->phaseParisRepository->getSelectedPlayers($compos);
        }

        return $this->render('journee/show.html.twig', [
            'journee' => $journee,
            'journeesDepartementales' => $journeesDepartementales,
            'journeesParis' => $journeesParis,
            'compos' => $compos,
            'selectedPlayers' => $selectedPlayers,
            'dispos' => $joueursDeclares,
            'joueursNonDeclares' => $joueursNonDeclares,
            'disposJoueur' => $disposJoueur,
            'competiteurs' => $competiteurs
        ]);
    }

    /**
     * @Route("/journee/edit/{type}/{id}", name="journee.edit")
     * @param $type
     * @param PhaseDepartementale $compo
     * @param Request $request
     * @return Response
     */
    public function edit($type, PhaseDepartementale $compo, Request $request) : Response
    { //TODO Considéré selon le type
        $oldPlayers = $this->phaseDepartementaleRepository->findOneBy(['id' => $compo->getId()]);
        $j1 = $oldPlayers->getIdJoueur1();
        $j2 = $oldPlayers->getIdJoueur2();
        $j3 = $oldPlayers->getIdJoueur3();
        $j4 = $oldPlayers->getIdJoueur4();

        $form = $this->createForm(PhaseDepartementaleType::class, $compo);
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
                'type' => $compo->getIdJournee()->getLinkType(),
                'id' => $compo->getIdJournee()->getNJournee()
            ]);
        }

        $burntPlayers = $this->competiteurRepository->findBurnPlayers($compo->getIdEquipe());
        $almostBurntPlayers = $this->competiteurRepository->findAlmostBurnPlayers($compo->getIdEquipe());
        $journeesDepartementales = $this->journeeDepartementaleRepository->findAll();
        $journeesParis = $this->journeeParisRepository->findAll();
        return $this->render('journee/edit.html.twig', [
            'burntPlayers' => $burntPlayers,
            'almostBurntPlayers' => $almostBurntPlayers,
            'journeesDepartementales' => $journeesDepartementales,
            'journeesParis' => $journeesParis,
            'compo' => $compo,
            'form' => $form->createView()
        ]);
    }
}
