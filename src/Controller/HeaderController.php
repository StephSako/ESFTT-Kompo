<?php

namespace App\Controller;

use App\Repository\ChampionnatRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\RencontreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HeaderController extends AbstractController
{
    private $championnatRepository;
    private $disponibiliteRepository;
    private $rencontreRepository;
    private $utilController;

    /**
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param RencontreRepository $rencontreRepository
     * @param UtilController $utilController
     */
    public function __construct(ChampionnatRepository $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                RencontreRepository $rencontreRepository,
                                UtilController $utilController)
    {
        $this->rencontreRepository = $rencontreRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->championnatRepository = $championnatRepository;
        $this->utilController = $utilController;
    }
    /**
     * Retourne toutes les valeurs nécessaires à l'affichage du header
     * @return array
     */
    public function getHeaderData(): array {
        if (!$this->get('session')->get('type')) $championnat = $this->utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $this->utilController->nextJourneeToPlayAllChamps()->getIdChampionnat());

        // Disponibilités du joueur
        $id = $championnat->getIdChampionnat();
        $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
        $disposJoueurFormatted = null;
        if ($this->getUser()->isCompetiteur()) {
            $disposJoueurFormatted = [];
            foreach($disposJoueur as $dispo) {
                $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
            }
        }

        $journees = $championnat->getJournees()->toArray();
        $allChampionnats = $this->championnatRepository->findAll();

        return [
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'disposJoueur' => $disposJoueurFormatted,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids'],
        ];
    }
}