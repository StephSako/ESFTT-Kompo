<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi\Model\Rencontre;


class RencontreDetails
{
    private $nomEquipeA;
    private $nomEquipeB;
    private $scoreEquipeA;
    private $scoreEquipeB;
    private $joueursA;
    private $joueursB;
    private $parties;
    /**
     * @var int
     */
    private $expectedScoreEquipeA;
    /**
     * @var int
     */
    private $expectedScoreEquipeB;

    /**
     * @param  $nomEquipeA string
     * @param $nomEquipeB string
     * @param $scoreEquipeA int
     * @param $scoreEquipeB int
     * @param $joueursA Joueur[]
     * @param $joueursB Joueur[]
     * @param $parties Partie[]
     */
    public function __construct(
        string $nomEquipeA,
        string $nomEquipeB,
        int $scoreEquipeA,
        int $scoreEquipeB,
        array $joueursA,
        array $joueursB,
        array $parties,
        float $expectedScoreEquipeA,
        float $expectedScoreEquipeB
    )
    {
        $this->nomEquipeA = $nomEquipeA;
        $this->nomEquipeB = $nomEquipeB;
        $this->scoreEquipeA = $scoreEquipeA;
        $this->scoreEquipeB = $scoreEquipeB;
        $this->joueursA = $joueursA;
        $this->joueursB = $joueursB;
        $this->parties = $parties;
        $this->expectedScoreEquipeA = $expectedScoreEquipeA;
        $this->expectedScoreEquipeB = $expectedScoreEquipeB;
    }

    public function getNomEquipeA(): string
    {
        return $this->nomEquipeA;
    }

    public function getNomEquipeB(): string
    {
        return $this->nomEquipeB;
    }

    public function getScoreEquipeA(): int
    {
        return $this->scoreEquipeA;
    }

    public function getScoreEquipeB(): int
    {
        return $this->scoreEquipeB;
    }

    public function getJoueursA()
    {
        return $this->joueursA;
    }

    public function getJoueursB()
    {
        return $this->joueursB;
    }

    public function getParties()
    {
        return $this->parties;
    }

    /**
     * @return float
     */
    public function getExpectedScoreEquipeA(): float
    {
        return $this->expectedScoreEquipeA;
    }

    /**
     * @return float
     */
    public function getExpectedScoreEquipeB(): float
    {
        return $this->expectedScoreEquipeB;
    }
}