<?php
/**
 * Created by Stephen Sakovitch.
 */

namespace FFTTApi\Model;


class VirtualPoints
{
    private $seasonlyPointsWon;
    private $monthlyPointsWon;
    private $virtualPoints;
    private $mensualPoints;
    private $matches;

    public function __construct(float $mensualPoints, float $monthlyPointsWon, float $virtualPoints, float $seasonlyPointsWon, array $matches)
    {
        $this->monthlyPointsWon = $monthlyPointsWon;
        $this->virtualPoints = $virtualPoints;
        $this->seasonlyPointsWon = $seasonlyPointsWon;
        $this->mensualPoints = $mensualPoints;
        $this->matches = $matches;
    }

    /**
     * @return float
     */
    public function getMensualPoints(): float
    {
        return $this->mensualPoints;
    }

    /**
     * @param float $mensualPoints
     * @return VirtualPoints
     */
    public function setMensualPoints(float $mensualPoints): VirtualPoints
    {
        $this->mensualPoints = $mensualPoints;
        return $this;
    }

    /**
     * @return float
     */
    public function getMonthlyPointsWon(): float
    {
        return $this->monthlyPointsWon;
    }

    /**
     * @param float $monthlyPointsWon
     * @return VirtualPoints
     */
    public function setMonthlyPointsWon(float $monthlyPointsWon): VirtualPoints
    {
        $this->monthlyPointsWon = $monthlyPointsWon;
        return $this;
    }

    /**
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * @param array $matches
     * @return VirtualPoints
     */
    public function setMatches(array $matches): VirtualPoints
    {
        $this->matches = $matches;
        return $this;
    }

    public function getSeasonlyPointsWon(): float
    {
        return $this->seasonlyPointsWon;
    }

    public function getPointsWon(): float
    {
        return $this->monthlyPointsWon;
    }

    public function getVirtualPoints(): float
    {
        return $this->virtualPoints;
    }
}