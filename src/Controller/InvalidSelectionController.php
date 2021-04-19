<?php

namespace App\Controller;

use App\Entity\Championnat;
use App\Entity\Rencontre;
use App\Repository\ChampionnatRepository;
use App\Repository\RencontreRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvalidSelectionController extends AbstractController
{
    private $rencontreRepository;
    private $championnatRepository;

    /**
     * @param ChampionnatRepository $championnatRepository
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(ChampionnatRepository $championnatRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->rencontreRepository = $rencontreRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @param Championnat $championnat
     * @param Rencontre $compo
     * @param int $idJoueur
     * @param int $nbJoueurs
     * @throws Exception
     */
    public function checkInvalidSelection(Championnat $championnat, Rencontre $compo, int $idJoueur, int $nbJoueurs){
        if ($idJoueur != null && $compo->getIdJournee()->getIdJournee() < $championnat->getNbJournees()) {
            $this->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenBurnt($idJoueur, $compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getNumero(), $championnat->getLimiteBrulage(), $nbJoueurs, $championnat->getIdChampionnat()), $nbJoueurs);
        }
    }

    /**
     * @param $invalidCompos
     * @param int $nbJoueurs
     */
    public function deleteInvalidSelectedPlayers($invalidCompos, int $nbJoueurs){
        foreach ($invalidCompos as $compo){
            $i = 0;
            while($i != $nbJoueurs){
                if (boolval($compo['isPlayer' . $i])){
                    $compo['compo']->setIdJoueurN($i, NULL);
                    break;
                }
                $i++;
            }
        }
    }
}
