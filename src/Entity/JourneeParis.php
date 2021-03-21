<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JourneeParisRepository")
 * @ORM\Table(name="prive_journee_paris")
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
     * @ORM\Column(type="date", name="date", nullable=false)
     */
    private $date;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="undefined", nullable=false)
     */
    private $undefined;

    /**
     * @var String
     */
    private $type = 'Paris';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RencontreParis", mappedBy="idJournee")
     */
    protected $rencontres;

    /**
     * @return String
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getRencontres()
    {
        return $this->rencontres;
    }

    /**
     * @param mixed $rencontres
     * @return JourneeParis
     */
    public function setRencontres($rencontres): self
    {
        $this->rencontres = $rencontres;
        return $this;
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
     * @return JourneeParis
     */
    public function setIdJournee($idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return JourneeParis
     */
    public function setDate(Datetime $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUndefined(): bool
    {
        return $this->undefined;
    }

    /**
     * @param bool $undefined
     * @return JourneeParis
     */
    public function setUndefined(bool $undefined): self
    {
        $this->undefined = $undefined;
        return $this;
    }
}