<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @var string|null
     *
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Le log de mise à jour doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @ORM\Column(type="string", name="last_update", nullable=true, length=100)
     */
    private $lastUpdate;

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

    public function isOver(): bool
    {
        return !$this->getUndefined()
            && intval((new DateTime())->diff($this->getDateJournee())->format('%R%a')) < 0
            && !count(array_filter($this->getRencontres()->toArray(), function ($r) {
                return !$r->isOver();
            }));
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
     * @return string|null
     */
    public function getLastUpdate(): ?string
    {
        return $this->lastUpdate;
    }

    /**
     * @param string|null $lastUpdate
     * @return Journee
     */
    public function setLastUpdate(?string $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }
}