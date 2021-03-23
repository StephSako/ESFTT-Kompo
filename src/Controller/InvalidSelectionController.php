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
            if ($type === 'departementale') $this->deleteInvalidSelectedPlayers($this->rencontreDepartementaleRepository->getSelectedWhenBurnt($jForm, $compo->getIdJournee(), $compo->getIdEquipe(), $this->getParameter('limite_brulage_dep') - 1), 'departementale');
            else if ($type === 'paris') $this->deleteInvalidSelectedPlayers($this->rencontreParisRepository->getSelectedWhenBurnt($jForm, $compo->getIdJournee(), $compo->getIdEquipe(), $this->getParameter('limite_brulage_dep') - 1), 'paris');
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
}
