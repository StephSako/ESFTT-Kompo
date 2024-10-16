<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DivisionRepository")
 * @ORM\Table(
 *     name="prive_division",
 *     indexes={
 *         @Index(name="IDX_div_champ", columns={"id_championnat"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_div_sn", columns={"short_name", "id_championnat"}),
 *          @UniqueConstraint(name="UNIQ_div_ln", columns={"long_name",  "id_championnat"})
 *     }
 * )
 */
class Division
{
    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Equipe", mappedBy="idDivision")
     */
    protected $equipes;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_division", nullable=false)
     */
    private $idDivision;
    /**
     * @var Championnat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championnat", inversedBy="divisions")
     * @ORM\JoinColumn(name="id_championnat", referencedColumnName="id_championnat", nullable=false)
     */
    private $idChampionnat;
    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 2,
     *      minMessage = "Le diminitif doit contenir au moins {{ limit }} lettres",
     *      maxMessage = "Le diminitif doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="string", name="short_name", nullable=false, length=2)
     */
    private $shortName;
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} lettres",
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @ORM\Column(type="string", name="long_name", nullable=false, length=25)
     */
    private $longName;
    /**
     * @var int
     *
     * @Assert\GreaterThanOrEqual(
     *     value = 2,
     *     message = "Le nombre minimal de joueurs est {{ compared_value }}"
     * )
     *
     * @Assert\LessThanOrEqual(
     *     value = 9,
     *     message = "Le nombre maximal de joueurs est {{ compared_value }}"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="integer", name="nb_joueurs", nullable=false)
     */
    private $nbJoueurs;
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
     * @var int
     *
     * @ORM\Column(type="integer", name="lien_fftt_api", nullable=false)
     */
    private $organismePere;

    /**
     * @return mixed
     */
    public function getIdDivision()
    {
        return $this->idDivision;
    }

    /**
     * @param mixed $idDivision
     * @return Division
     */
    public function setIdDivision($idDivision): self
    {
        $this->idDivision = $idDivision;
        return $this;
    }

    /**
     * @return Championnat|null
     */
    public function getIdChampionnat(): ?Championnat
    {
        return $this->idChampionnat;
    }

    /**
     * @param Championnat $idChampionnat
     * @return Division
     */
    public function setIdChampionnat(Championnat $idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string|null $shortName
     * @return Division
     */
    public function setShortName(?string $shortName): self
    {
        $this->shortName = mb_convert_case($shortName, MB_CASE_UPPER, "UTF-8");
        return $this;
    }

    /**
     * @return string
     */
    public function getLongName(): string
    {
        return $this->longName;
    }

    /**
     * @param string|null $longName
     * @return Division
     */
    public function setLongName(?string $longName): self
    {
        $this->longName = mb_convert_case($longName, MB_CASE_TITLE, "UTF-8");
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    /**
     * @param Collection $equipes
     * @return Division
     */
    public function setEquipes(Collection $equipes): self
    {
        $this->equipes = $equipes;
        return $this;
    }

    /**
     * @return int
     */
    public function getNbJoueurs(): int
    {
        return $this->nbJoueurs;
    }

    /**
     * @param int $nbJoueurs
     * @return Division
     */
    public function setNbJoueurs(int $nbJoueurs): self
    {
        $this->nbJoueurs = $nbJoueurs;
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
     * @return Division
     */
    public function setLastUpdate(?string $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrganismePere(): int
    {
        return $this->organismePere;
    }

    /**
     * @param int $organismePere
     * @return Division
     */
    public function setOrganismePere(int $organismePere): self
    {
        $this->organismePere = $organismePere;
        return $this;
    }
}