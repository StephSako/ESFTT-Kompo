<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JourneeRepository")
 * @ORM\Table(
 *     name="prive_journee",
 *     indexes={
 *          @Index(name="IDX_j_champ", columns={"id_championnat"}),
 *     }
 * )
 */
class Journee
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rencontre", mappedBy="idJournee", cascade={"remove"}, orphanRemoval=true)
     */
    protected $rencontres;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_journee")
     */
    private $idJournee;
    /**
     * @var DateTime|null
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
     * @var bool|null
     */
    private $undefined;

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
     * @return string
     */
    public function getDateJourneeFrench(): string
    {
        return mb_convert_case(strftime("%A %d %B %Y", $this->getDateJournee()->getTimestamp()), MB_CASE_TITLE, "UTF-8");
    }

    /**
     * @return DateTime|null
     */
    public function getDateJournee(): ?DateTime
    {
        return $this->dateJournee;
    }

    /**
     * @param DateTime|null $dateJournee
     * @return Journee
     */
    public function setDateJournee(?Datetime $dateJournee): self
    {
        $this->dateJournee = $dateJournee;
        return $this;
    }

    /**
     * Détermine si une journée est passée ou non
     * @return bool
     */
    public function isOver(): bool
    {
        return false; //TODO DELETE
//        return !$this->getUndefined()
//            && intval((new DateTime())->diff($this->getDateJournee())->format('%R%a')) < 0
//            && !count(array_filter($this->getRencontres()->toArray(), function ($r) {
//                return !$r->isOver();
//            }));
    }

    /**
     * Récupère la date au plus tard entre la date de la journée et les dates de report de chacunes de ses rencontres
     */
    public function getLatestDate(): DateTime
    {
        $datesEtReportsJournee = array_map(function ($rencontre) {
            return $rencontre->isReporte() ? $rencontre->getDateReport() : $this->getDateJournee();
        }, $this->getRencontres()->toArray());

        return count($datesEtReportsJournee) ? max(max($datesEtReportsJournee), $this->getDateJournee()) : $this->getDateJournee();
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
     * @return bool|null
     */
    public function isUndefined(): ?bool
    {
        return $this->undefined;
    }

    /**
     * @param bool|null $undefined
     * @return Journee
     */
    public function setUndefined(?bool $undefined): self
    {
        $this->undefined = $undefined;
        return $this;
    }
}