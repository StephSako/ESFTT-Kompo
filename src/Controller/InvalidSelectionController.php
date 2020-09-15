<?php

namespace App\Controller;

use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class InvalidSelectionController extends AbstractController
{
    private $em;
    private $competiteurRepository;
    private $disponibiliteDepartementaleRepository;
    private $disponibiliteParisRepository;
    private $journeeDepartementaleRepository;
    private $journeeParisRepository;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;

    /**
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
    }

    /**
     * @param $j
     * @param $invalidCompo
     * @Route("/products")
     */
    public function deleteInvalidSelectedPlayerDepartementale($j, $invalidCompo){
        foreach ($invalidCompo as $compo){
            if ($compo["isPlayer1"]) $compo["compo"]->setIdJoueur1(NULL);
            if ($compo["isPlayer2"]) $compo["compo"]->setIdJoueur2(NULL);
            if ($compo["isPlayer3"]) $compo["compo"]->setIdJoueur3(NULL);
            if ($compo["isPlayer4"]) $compo["compo"]->setIdJoueur4(NULL);
            $this->decrementeBrulage($j, 'departementale', $compo["compo"]);
            $this->em->flush();
        }
    }

    /**
     * @param $j
     * @param $invalidCompo
     */
    public function deleteInvalidSelectedPlayerParis($j, $invalidCompo){
        foreach ($invalidCompo as $compo){
            if ($compo["isPlayer1"]) $compo["compo"]->setIdJoueur1(NULL);
            if ($compo["isPlayer2"]) $compo["compo"]->setIdJoueur2(NULL);
            if ($compo["isPlayer3"]) $compo["compo"]->setIdJoueur3(NULL);
            if ($compo["isPlayer4"]) $compo["compo"]->setIdJoueur4(NULL);
            if ($compo["isPlayer5"]) $compo["compo"]->setIdJoueur5(NULL);
            if ($compo["isPlayer6"]) $compo["compo"]->setIdJoueur6(NULL);
            if ($compo["isPlayer7"]) $compo["compo"]->setIdJoueur7(NULL);
            if ($compo["isPlayer8"]) $compo["compo"]->setIdJoueur8(NULL);
            if ($compo["isPlayer9"]) $compo["compo"]->setIdJoueur9(NULL);
            $this->decrementeBrulage($j, 'paris', $compo["compo"]);
            $this->em->flush();
        }
    }

    /**
     * @param $type
     * @param $compo
     * @param $jForm
     * @param $jCompo
     */
    public function incrementeBrulage($type, $compo, $jForm, $jCompo){
        if ($jForm != null) {
            if ($type === 'departementale') {
                $brulage = $jForm->getBrulageDepartemental();
                $brulage[$compo->getIdEquipe()->getIdEquipe()]++;
                $jCompo->setBrulageDepartemental($brulage);
                $this->em->flush();

                /** On vérifie si le joueur n'est pas brûlé et selectionné dans de futures compositions **/
                $this->deleteInvalidSelectedPlayerDepartementale($jForm, $this->rencontreDepartementaleRepository->getSelectedWhenBurnt($jForm, $compo->getIdJournee(), $compo->getIdEquipe()));
            } else if ($type === 'paris') {
                $brulage = $jForm->getBrulageParis();
                $brulage[$compo->getIdEquipe()->getIdEquipe()]++;
                $jCompo->setBrulageParis($brulage);
                $this->em->flush();

                /** On vérifie si le joueur n'est pas brûlé et selectionné dans de futures compositions **/
                $this->deleteInvalidSelectedPlayerParis($jForm, $this->rencontreParisRepository->getSelectedWhenBurnt($jForm, $compo->getIdJournee()));
            }
        }
    }

    /**
     * @param $j
     * @param $type
     * @param $compo
     */
    public function decrementeBrulage($j, $type, $compo){
        if ($j != null) {
            if ($type === 'departementale') {
                $brulageOld = $j->getBrulageDepartemental();
                $brulageOld[$compo->getIdEquipe()->getIdEquipe()]--;
                $j->setBrulageDepartemental($brulageOld);
            } else if ($type === 'paris') {
                $brulageOld = $j->getBrulageParis();
                $brulageOld[$compo->getIdEquipe()->getIdEquipe()]--;
                $j->setBrulageParis($brulageOld);
            }
        }
    }
}
