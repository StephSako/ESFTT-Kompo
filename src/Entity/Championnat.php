<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChampionnatRepository")
 * @ORM\Table(
 *     name="prive_championnat",
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_champ", columns={"nom"}),
 *          @UniqueConstraint(name="UNIQ_lien_fftt", columns={"lien_fftt_api"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"nom"}
 * )
 */
class Championnat
{
    /** Périodicités d'un championnat */
    const PERIODICITE = [
        'Phase' => true,
        'Saison' => false
    ];

    /** Type d'épreuve */
    const TYPE_EPREUVE = [
        'Championnat de France par Equipes Masculin' => 'FED_Championnat de France par Equipes Masculin',
        'Championnat de Paris IDF' => 'L08_Championnat de Paris IDF'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="string", name="nom", nullable=false, length=50)
     */
    private $nom;

    /**
     * @var int|null
     *
     * @Assert\GreaterThanOrEqual(
     *     value = 1,
     *     message = "La limite de brûlage doit être supérieure à {{ compared_value }}"
     * )
     *
     * @Assert\LessThanOrEqual(
     *     value = 4,
     *     message = "La limite de brûlage doit être inférierue à {{ compared_value }}"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="integer", name="limite_brulage", nullable=true)
     */
    private $limiteBrulage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="type_epreuve", nullable=false, length=100)
     */
    private $typeEpreuve;

    /**
     * @var int
     *
     * @Assert\GreaterThanOrEqual(
     *     value = 1,
     *     message = "Il doit y avoir au minimum {{ compared_value }} journée"
     * )
     *
     * @Assert\LessThanOrEqual (
     *     value = 10,
     *     message = "Il doit y avoir au maximum {{ compared_value }} journée"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="integer", name="nb_journees", nullable=false)
     */
    private $nbJournees;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="j2rule", nullable=false)
     */
    private $j2Rule;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="compo_sorted", nullable=false)
     */
    private $compoSorted;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="periodicite", nullable=false)
     */
    private $periodicite;

    /**
     * @var Rencontre[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Rencontre", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $rencontres;

    /**
     * @var Division[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Division", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $divisions;

    /**
     * @var Disponibilite[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Disponibilite", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $dispos;

    /**
     * @var Equipe[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Equipe", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $equipes;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Journee", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $journees;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Titularisation", mappedBy="idChampionnat")
     */
    private $titularisations;

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
     * @return int
     */
    public function getNbJournees(): int
    {
        return $this->nbJournees;
    }

    /**
     * @param int $nbJournees
     * @return Championnat
     */
    public function setNbJournees(int $nbJournees): self
    {
        $this->nbJournees = $nbJournees;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimiteBrulage(): ?int
    {
        return $this->limiteBrulage;
    }

    /**
     * @param int|null $limiteBrulage
     * @return Championnat
     */
    public function setLimiteBrulage(?int $limiteBrulage): self
    {
        $this->limiteBrulage = $limiteBrulage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDispos()
    {
        return $this->dispos;
    }

    /**
     * @param mixed $dispos
     * @return Championnat
     */
    public function setDispos($dispos): self
    {
        $this->dispos = $dispos;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEquipes()
    {
        return $this->equipes;
    }

    /**
     * @param mixed $equipes
     * @return Championnat
     */
    public function setEquipes($equipes): self
    {
        $this->equipes = $equipes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDivisions()
    {
        return $this->divisions;
    }

    /**
     * @param mixed $divisions
     * @return Championnat
     */
    public function setDivisions($divisions): self
    {
        $this->divisions = $divisions;
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
     * @param mixed $rencontres
     * @return Championnat
     */
    public function setRencontres($rencontres): self
    {
        $this->rencontres = $rencontres;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdChampionnat()
    {
        return $this->idChampionnat;
    }

    /**
     * @param mixed $idChampionnat
     * @return Championnat
     */
    public function setIdChampionnat($idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     * @return Championnat
     */
    public function setNom(?string $nom): self
    {
        $this->nom = mb_convert_case($nom, MB_CASE_TITLE, "UTF-8");
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return (new Slugify())->slugify($this->nom);
    }

    /**
     * @return bool
     */
    public function isJ2Rule(): bool
    {
        return $this->j2Rule;
    }

    /**
     * @param bool $j2Rule
     * @return Championnat
     */
    public function setJ2Rule(bool $j2Rule): self
    {
        $this->j2Rule = $j2Rule;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeEpreuve(): string
    {
        return $this->typeEpreuve;
    }

    /**
     * @param string $typeEpreuve
     * @return Championnat
     */
    public function setTypeEpreuve(string $typeEpreuve): Championnat
    {
        $this->typeEpreuve = $typeEpreuve;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCompoSorted(): bool
    {
        return $this->compoSorted;
    }

    /**
     * @param bool $compoSorted
     * @return Championnat
     */
    public function setCompoSorted(bool $compoSorted): self
    {
        $this->compoSorted = $compoSorted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPeriodicite(): bool
    {
        return $this->periodicite;
    }

    /**
     * @param bool $periodicite
     * @return Championnat
     */
    public function setPeriodicite(bool $periodicite): self
    {
        $this->periodicite = $periodicite;
        return $this;
    }

    /**
     * Retourne l'ID de la prochaine journée à jouer
     * @return Journee|null
     */
    public function getNextJourneeToPlay(): ?Journee
    {
        $nextJourneeToPlay = array_filter($this->getJournees()->toArray(), function ($journee) {
            return !$journee->getUndefined() && (int)(new DateTime())->diff($journee->getDateJournee())->format('%R%a') >= 0;
        });
        return array_shift($nextJourneeToPlay) ?: null;
    }

    /**
     * @return Collection
     */
    public function getJournees(): Collection
    {
        return $this->journees;
    }

    /**
     * @param Collection $journees
     * @return Championnat
     */
    public function setJournees(Collection $journees): self
    {
        $this->journees = $journees;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getTitularisations(): Collection
    {
        return $this->titularisations;
    }

    /**
     * @param Collection $titularisations
     * @return Championnat
     */
    public function setTitularisations(Collection $titularisations): self
    {
        $this->titularisations = $titularisations;
        return $this;
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
     * @return Championnat
     */
    public function setLastUpdate(?string $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }
}