<?php

namespace App\Controller;

use App\Entity\RencontreDepartementale;
use App\Entity\RencontreParis;
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
     * @param RencontreDepartementale|RencontreParis $compo
     * @param int $idJoueur
     * @param int $nbJournees
     * @param int $nbJoueurs
     */
    public function checkInvalidSelection($type, $compo, int $idJoueur, int $nbJournees, int $nbJoueurs){
        if ($idJoueur != null && $compo->getIdJournee()->getIdJournee() < $nbJournees) {
            if ($type === 'departementale') $this->deleteInvalidSelectedPlayers($this->rencontreDepartementaleRepository->getSelectedWhenBurnt($idJoueur, $compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getNumero(), $this->getParameter('limite_brulage_departementale'), $nbJoueurs), $nbJoueurs);
            else if ($type === 'paris') $this->deleteInvalidSelectedPlayers($this->rencontreParisRepository->getSelectedWhenBurnt($idJoueur, $compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getNumero(), $this->getParameter('limite_brulage_paris'), $nbJoueurs), $nbJoueurs);
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
