<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JourneeDepartementaleRepository")
 * @ORM\Table(name="prive_journee_departementale")
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
     * @var DateTime
     *
     * @ORM\Column(type="date", name="date", nullable=false, unique=true)
     */
    private $date;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="undefined", nullable=false)
     */
    private $undefined;

    /**
     * @var string
     */
    private $type = 'Départemental';

    /**
     * @var string
     */
    private $linkType = 'departementale';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RencontreDepartementale", mappedBy="idJournee")
     */
    protected $rencontres;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLinkType(): string
    {
        return $this->linkType;
    }

    /**
     * @return mixed
     */
    public function getRencontres()
    {
        return $this->rencontres;
    }

    /**
     * @param $rencontres
     * @return $this
     */
    public function setRencontres($rencontres): self
    {
        $this->rencontres = $rencontres;
        return $this;
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
     * @return JourneeDepartementale
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
     * @return JourneeDepartementale
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
     * @return JourneeDepartementale
     */
    public function setUndefined(bool $undefined): self
    {
        $this->undefined = $undefined;
        return $this;
    }
}