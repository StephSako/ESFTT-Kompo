<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="JourneeRepository")
 * @ORM\Table(
 *     name="prive_journee_departementale",
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_jour_dep_date", columns={"date_journee"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"dateJournee"}
 * )
 */
class Journee
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
     * @ORM\Column(type="date", name="date_journee", nullable=false)
     */
    private $dateJournee;

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
     * @return Journee
     */
    public function setIdJournee($idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateJournee(): DateTime
    {
        return $this->dateJournee;
    }

    /**
     * @param DateTime $dateJournee
     * @return Journee
     */
    public function setDateJournee(Datetime $dateJournee): self
    {
        $this->dateJournee = $dateJournee;
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
     * @return Journee
     */
    public function setUndefined(bool $undefined): self
    {
        $this->undefined = $undefined;
        return $this;
    }
}