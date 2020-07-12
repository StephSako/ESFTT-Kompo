<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JourneeDepartementaleRepository")
 * @ORM\Table(name="journee_departementale")
 */
class JourneeDepartementale
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_journee")
     */
    private $idJournee;

    /**
     * @ORM\Column(type="integer", name="n_journee")
     */
    private $nJournee;

    /**
     * @var DateTime
     * @ORM\Column(type="date", name="date")
     */
    private $date;

    /**
     * @return mixed
     */
    public function getIdJournee()
    {
        return $this->idJournee;
    }

    /**
     * @param mixed $idJournee
     */
    public function setIdJournee($idJournee): void
    {
        $this->idJournee = $idJournee;
    }

    /**
     * @return int|null
     */
    public function getNJournee(): ?int
    {
        return $this->nJournee;
    }

    /**
     * @param mixed $nJournee
     */
    public function setNJournee($nJournee): void
    {
        $this->nJournee = $nJournee;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(Datetime $date): void
    {
        $this->date = $date;
    }
}