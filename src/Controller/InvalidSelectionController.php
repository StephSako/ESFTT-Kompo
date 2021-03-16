<?php

namespace App\Controller;

use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvalidSelectionController extends AbstractController
{
    private $em;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;

    /**
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
    }

    /**
     * @param $type
     * @param $compo
     * @param $jForm
     */
    public function checkInvalidSelection($type, $compo, $jForm){
        if ($jForm != null && $compo->getIdJournee()->getIdJournee() < 7) {
            if ($type === 'departementale') $this->deleteInvalidSelectedPlayers($this->rencontreDepartementaleRepository->getSelectedWhenBurnt($jForm, $compo->getIdJournee(), $compo->getIdEquipe()), 'departementale');
            else if ($type === 'paris') $this->deleteInvalidSelectedPlayers($this->rencontreParisRepository->getSelectedWhenBurnt($jForm, $compo->getIdJournee()), 'paris');
        }
    }

    /**
     * @param $invalidCompo
     * @param $type
     */
    public function deleteInvalidSelectedPlayers($invalidCompo, $type){
        foreach ($invalidCompo as $compo){
            if ($compo["isPlayer1"]) $compo["compo"]->setIdJoueur1(NULL);
            if ($compo["isPlayer2"]) $compo["compo"]->setIdJoueur2(NULL);
            if ($compo["isPlayer3"]) $compo["compo"]->setIdJoueur3(NULL);
            if ($compo["isPlayer4"]) $compo["compo"]->setIdJoueur4(NULL);

            if ($type == 'paris') {
                if ($compo["isPlayer5"]) $compo["compo"]->setIdJoueur5(NULL);
                if ($compo["isPlayer6"]) $compo["compo"]->setIdJoueur6(NULL);
                if ($compo["isPlayer7"]) $compo["compo"]->setIdJoueur7(NULL);
                if ($compo["isPlayer8"]) $compo["compo"]->setIdJoueur8(NULL);
                if ($compo["isPlayer9"]) $compo["compo"]->setIdJoueur9(NULL);
            }
        }
    }

    /**
     * @param $compositions
     * @param $competiteur
     * @param $type
     */
    public function disengageDeletedPlayerInComposition($compositions, $competiteur, $type){
        foreach ($compositions as $composition) {
            if ($composition->getIdJoueur1() && $composition->getIdJoueur1()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur1(NULL);
            if ($composition->getIdJoueur2() && $composition->getIdJoueur2()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur2(NULL);
            if ($composition->getIdJoueur3() && $composition->getIdJoueur3()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur3(NULL);
            if ($composition->getIdJoueur4() && $composition->getIdJoueur4()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur4(NULL);

            if ($type == 'paris') {
                if ($composition->getIdJoueur5() && $composition->getIdJoueur5()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur5(NULL);
                if ($composition->getIdJoueur6() && $composition->getIdJoueur6()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur6(NULL);
                if ($composition->getIdJoueur7() && $composition->getIdJoueur7()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur7(NULL);
                if ($composition->getIdJoueur8() && $composition->getIdJoueur8()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur8(NULL);
                if ($composition->getIdJoueur9() && $composition->getIdJoueur9()->getIdCompetiteur() == $competiteur) $composition->setIdJoueur9(NULL);
            }
        }
    }
}
