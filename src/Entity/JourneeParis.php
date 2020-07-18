<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JourneeParisRepository")
 * @ORM\Table(name="journee_paris")
 */
class JourneeParis
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_journee")
     */
    private $idJournee;

    /**
     * @var DateTime
     * @ORM\Column(type="date", name="date")
     */
    private $date;

    /**
     * @var String
     */
    private $type = 'Champ. de Paris';

    /**
     * @return String
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @var String
     */
    private $linkType = 'paris';

    /**
     * @return String
     */
    public function getLinkType(): string
    {
        return $this->linkType;
    }

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