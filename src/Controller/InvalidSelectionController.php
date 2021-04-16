<?php

namespace App\Controller;

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
     * @param int $type
     * @param Rencontre $compo
     * @param int $idJoueur
     * @param int $nbJournees
     * @param int $nbJoueurs
     * @throws Exception
     */
    public function checkInvalidSelection(int $type, Rencontre $compo, int $idJoueur, int $nbJournees, int $nbJoueurs){
        if ((!$championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        if ($idJoueur != null && $compo->getIdJournee()->getIdJournee() < $nbJournees) {
            $this->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenBurnt($idJoueur, $compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getNumero(), $championnat->getLimiteBrulage(), $nbJoueurs, $type), $nbJoueurs);
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
