<?php
/**
 * Created by Stephen Sakovitch.
 */

namespace FFTTApi\Model;


class CalculatedUnvalidatedPartie
{
    private $adversaire;
    private $isForfait;
    private $isVictoire;
    private $pointsAdversaire;
    private $pointsGagnes;
    private $date;
    private $epreuve;
    private $coefficient;

    /**
     * @param string $adversaire
     * @param bool $isForfait
     * @param bool $isVictoire
     * @param float $pointsAdversaire
     * @param float $pointsGagnes
     * @param string $date
     * @param string $epreuve
     * @param float $coefficient
     */
    public function __construct(
        string $adversaire,
        bool $isForfait,
        bool $isVictoire,
        float $pointsAdversaire,
        float $pointsGagnes,
        string $date,
        string $epreuve,
        float $coefficient
    )
    {
        $this->adversaire = $adversaire;
        $this->isForfait = $isForfait;
        $this->isVictoire = $isVictoire;
        $this->pointsAdversaire = $pointsAdversaire;
        $this->pointsGagnes = $pointsGagnes;
        $this->date = $date;
        $this->epreuve = $epreuve;
        $this->coefficient = $coefficient;
    }

    /**
     * @return string
     */
    public function getAdversaire(): string
    {
        return $this->adversaire;
    }

    /**
     * @param string $adversaire
     * @return CalculatedUnvalidatedPartie
     */
    public function setAdversaire(string $adversaire): CalculatedUnvalidatedPartie
    {
        $this->adversaire = $adversaire;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForfait(): bool
    {
        return $this->isForfait;
    }

    /**
     * @param bool $isForfait
     * @return CalculatedUnvalidatedPartie
     */
    public function setIsForfait(bool $isForfait): CalculatedUnvalidatedPartie
    {
        $this->isForfait = $isForfait;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVictoire(): bool
    {
        return $this->isVictoire;
    }

    /**
     * @param bool $isVictoire
     * @return CalculatedUnvalidatedPartie
     */
    public function setIsVictoire(bool $isVictoire): CalculatedUnvalidatedPartie
    {
        $this->isVictoire = $isVictoire;
        return $this;
    }

    /**
     * @return float
     */
    public function getPointsAdversaire(): float
    {
        return $this->pointsAdversaire;
    }

    /**
     * @param float $pointsAdversaire
     * @return CalculatedUnvalidatedPartie
     */
    public function setPointsAdversaire(float $pointsAdversaire): CalculatedUnvalidatedPartie
    {
        $this->pointsAdversaire = $pointsAdversaire;
        return $this;
    }

    /**
     * @return float
     */
    public function getPointsGagnes(): float
    {
        return $this->pointsGagnes;
    }

    /**
     * @param float $pointsGagnes
     * @return CalculatedUnvalidatedPartie
     */
    public function setPointsGagnes(float $pointsGagnes): CalculatedUnvalidatedPartie
    {
        $this->pointsGagnes = $pointsGagnes;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return CalculatedUnvalidatedPartie
     */
    public function setDate(string $date): CalculatedUnvalidatedPartie
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getEpreuve(): string
    {
        return $this->epreuve;
    }

    /**
     * @param string $epreuve
     * @return CalculatedUnvalidatedPartie
     */
    public function setEpreuve(string $epreuve): CalculatedUnvalidatedPartie
    {
        $this->epreuve = $epreuve;
        return $this;
    }

    /**
     * @return float
     */
    public function getCoefficient(): float
    {
        return $this->coefficient;
    }

    /**
     * @param float $coefficient
     * @return CalculatedUnvalidatedPartie
     */
    public function setCoefficient(float $coefficient): CalculatedUnvalidatedPartie
    {
        $this->coefficient = $coefficient;
        return $this;
    }
}