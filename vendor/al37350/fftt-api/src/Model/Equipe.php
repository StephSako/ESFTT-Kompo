<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi\Model;


class Equipe
{
    private $libelle;
    private $division;
    private $lienDivision;
    private $epreuve;

    public function __construct(string $libelle, string $division, string $lienDivision, string $epreuve)
    {
        $this->libelle = $libelle;
        $this->division = $division;
        $this->lienDivision = $lienDivision;
        $this->epreuve = $epreuve;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function getDivision(): string
    {
        return $this->division;
    }

    public function getLienDivision(): string
    {
        return $this->lienDivision;
    }

    public function getEpreuve(): string
    {
        return $this->epreuve;
    }
}