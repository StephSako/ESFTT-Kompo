<?php

namespace App\Controller;

use App\Repository\RencontreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvalidSelectionController extends AbstractController
{
    private $rencontreRepository;

    /**
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(RencontreRepository $rencontreRepository)
    {
        $this->rencontreRepository = $rencontreRepository;
    }

    /**
     * @param int $limiteBrulage
     * @param int $idChampionnat
     * @param int $idJoueur
     * @param int $nbJoueurs
     * @param int $numEquipe
     * @param int $idJournee
     */
    public function checkInvalidSelection(int $limiteBrulage, int $idChampionnat, int $idJoueur, int $nbJoueurs, int $numEquipe, int $idJournee){
        $this->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenBurnt($idJoueur, $idJournee, $numEquipe, $limiteBrulage, $nbJoueurs, $idChampionnat), $nbJoueurs, $idJoueur);
    }

    /**
     * @param array $invalidCompos
     * @param int $nbJoueurs
     * @param int $idCompetiteur
     */
    public function deleteInvalidSelectedPlayers(array $invalidCompos, int $nbJoueurs, int $idCompetiteur){
        foreach ($invalidCompos as $compo){
            $i = 0;
            while($i != $nbJoueurs){
                if ($compo['isPlayer' . $i] == $idCompetiteur){
                    $compo['compo']->setIdJoueurN($i, null);
                    break;
                }
                $i++;
            }
        }
    }
}
