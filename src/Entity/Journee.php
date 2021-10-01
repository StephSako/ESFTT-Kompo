<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JourneeRepository")
 * @ORM\Table(
 *     name="prive_journee",
 *     indexes={
 *          @Index(name="IDX_j_champ", columns={"id_championnat"}),
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_journee", columns={"date_journee", "id_championnat"})
 *     }
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
     * @var Championnat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championnat", inversedBy="journees")
     * @ORM\JoinColumn(name="id_championnat", referencedColumnName="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="undefined", nullable=false)
     */
    private $undefined;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rencontre", mappedBy="idJournee", cascade={"remove"}, orphanRemoval=true)
     */
    protected $rencontres;

    /**
     * @return Championnat
     */
    public function getIdChampionnat(): Championnat
    {
        return $this->idChampionnat;
    }

    /**
     * @param Championnat $idChampionnat
     * @return Journee
     */
    public function setIdChampionnat(Championnat $idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
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
     * @return string
     */
    public function getDateJourneeFrench(): string {
        return mb_convert_case(strftime("%A %d %B %Y", $this->getDateJournee()->getTimestamp()), MB_CASE_TITLE, "UTF-8");
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