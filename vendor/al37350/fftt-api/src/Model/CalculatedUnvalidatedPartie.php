<?php
/**
 * Created by Stephen Sakovitch.
 */

namespace FFTTApi\Model;


class CalculatedUnvalidatedPartie
{
    private $adversaire;
    private $isVictoire;
    private $pointsAdversaire;
    private $pointsGagnes;
    private $date;
    private $epreuve;
    private $coefficient;

    /**
     * @param string $adversaire
     * @param bool $isVictoire
     * @param float $pointsAdversaire
     * @param float $pointsGagnes
     * @param string $date
     * @param string $epreuve
     * @param float $coefficient
     */
    public function __construct(string $adversaire, bool $isVictoire, float $pointsAdversaire, float $pointsGagnes, string $date, string $epreuve, float $coefficient)
    {
        $this->adversaire = $adversaire;
        $this->isVictoire = $isVictoire;
        $this->pointsAdversaire = $pointsAdversaire;
        $this->pointsGagnes = $pointsGagnes;
        $this->date = $date;
        $this->epreuve = $epreuve;
        $this->coefficient = $coefficient;
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

    /**
     * @return mixed
     */
    public function getAdversaire()
    {
        return $this->adversaire;
    }

    /**
     * @param mixed $adversaire
     * @return CalculatedUnvalidatedPartie
     */
    public function setAdversaire($adversaire)
    {
        $this->adversaire = $adversaire;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsVictoire()
    {
        return $this->isVictoire;
    }

    /**
     * @param mixed $isVictoire
     * @return CalculatedUnvalidatedPartie
     */
    public function setIsVictoire($isVictoire)
    {
        $this->isVictoire = $isVictoire;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPointsAdversaire()
    {
        return $this->pointsAdversaire;
    }

    /**
     * @param mixed $pointsAdversaire
     * @return CalculatedUnvalidatedPartie
     */
    public function setPointsAdversaire($pointsAdversaire)
    {
        $this->pointsAdversaire = $pointsAdversaire;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPointsGagnes()
    {
        return $this->pointsGagnes;
    }

    /**
     * @param mixed $pointsGagnes
     * @return CalculatedUnvalidatedPartie
     */
    public function setPointsGagnes($pointsGagnes)
    {
        $this->pointsGagnes = $pointsGagnes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return CalculatedUnvalidatedPartie
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEpreuve()
    {
        return $this->epreuve;
    }

    /**
     * @param mixed $epreuve
     * @return CalculatedUnvalidatedPartie
     */
    public function setEpreuve($epreuve)
    {
        $this->epreuve = $epreuve;
        return $this;
    }
}