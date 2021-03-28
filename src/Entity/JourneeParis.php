<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JourneeParisRepository")
 * @ORM\Table(
 *     name="prive_journee_paris",
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_jour_par_date", columns={"date"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"date"}
 * )
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
     *
     * @ORM\Column(type="date", name="date", nullable=false)
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
    private $linkType = 'paris';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RencontreParis", mappedBy="idJournee")
     */
    protected $rencontres;

    /**
     * @return mixed
     */
    public function getRencontres()
    {
        return $this->rencontres;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     * @return string
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