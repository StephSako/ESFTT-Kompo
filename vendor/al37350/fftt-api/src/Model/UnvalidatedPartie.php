<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi\Model;


class UnvalidatedPartie
{
    private $epreuve;
    private $idPartie;
    private $coefficientChampionnat;
    private $isVictoire;
    private $isForfait;
    private $date;
    private $adversaireNom;
    private $adversairePrenom;
    private $adversaireClassement;

    public function __construct(
        string $epreuve,
        string $idPartie,
        float $coefficientChampionnat,
        bool $isVictoire,
        bool $isForfait,
        \DateTime $date,
        string $adversaireNom,
        string $adversairePrenom,
        int $adversaireClassement
    )
    {
        $this->isVictoire = $isVictoire;
        $this->idPartie = $idPartie;
        $this->isForfait = $isForfait;
        $this->date = $date;
        $this->adversaireNom = $adversaireNom;
        $this->adversairePrenom = $adversairePrenom;
        $this->adversaireClassement = $adversaireClassement;
        $this->coefficientChampionnat = $coefficientChampionnat;
        $this->epreuve = $epreuve;
    }

    public function isVictoire(): bool
    {
        return $this->isVictoire;
    }

    public function isForfait(): bool
    {
        return $this->isForfait;
    }

    public function getEpreuve(): string
    {
        return $this->epreuve;
    }
    
    public function getIdPartie(): string
    {
        return $this->idPartie;
    }

    public function getCoefficientChampionnat(): float
    {
        return $this->coefficientChampionnat;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getAdversaireNom(): string
    {
        return $this->adversaireNom;
    }

    public function getAdversairePrenom(): string
    {
        return $this->adversairePrenom;
    }

    public function getAdversaireClassement(): int
    {
        return $this->adversaireClassement;
    }
}
