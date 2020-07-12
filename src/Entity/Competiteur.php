<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompetiteurRepository")
 * @ORM\Table(name="competiteur")
 */
class Competiteur implements UserInterface, Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_competiteur")
     */
    private $idCompetiteur;

    /**
     * @ORM\Column(name="license", type="integer", length=11)
     */
    private $license;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, name="nom")
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=250, nullable=false)
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 250,
     *      minMessage = "Votre mot de passe doit contenir au moins {{ limit }} letttres",
     *      maxMessage = "Votre mot de passe doit contenir au maximum {{ limit }} letttres",
     *     )
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50, nullable=false)
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 30,
     *      minMessage = "Votre pseudo doit contenir au moins {{ limit }} letttres",
     *      maxMessage = "Votre pseudo doit contenir au maximum {{ limit }} letttres",
     *     )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=1, name="role")
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, name="avatar")
     */
    private $avatar;

    /**
     * @ORM\Column(type="json", name="brulage")
     */
    private $brulage;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DisponibiliteDepartementale", mappedBy="idCompetiteur")
     */
    private $disposDepartementales;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DisponibiliteParis", mappedBy="idCompetiteur")
     */
    private $disposParis;
    //TODO Issue

    /**
     * @return array
     */
    public function getBrulage(): array
    {
        return $this->brulage;
    }

    /**
     * @param mixed $brulage
     * @return Competiteur
     */
    public function setBrulage($brulage): self
    {
        $this->brulage = $brulage;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdCompetiteur(): int
    {
        return $this->idCompetiteur;
    }

    /**
     * @return int|null
     */
    public function getLicense(): ?int
    {
        return $this->license;
    }

    /**
     * @param int $license
     * @return $this
     */
    public function setLicense(int $license): self
    {
        $this->license = $license;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     * @return $this
     */
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return $this
     */
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Competiteur
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string $username
     * @return Competiteur
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getRoles()
    {
        if ($this->getRole() == 'C') {
            return ['ROLE_CAPITAINE', 'ROLE_JOUEUR'];
        } else {
            return ['ROLE_JOUEUR'];
        }
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    { }

    public function serialize()
    {
        return serialize([
            $this->idCompetiteur,
            $this->username,
            $this->password
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->idCompetiteur,
            $this->username,
            $this->password
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @param $idEquipe
     * @return string
     */
    public function getPlayersChips($idEquipe){
        return "<div class='chip'><img src='" . $this->getAvatar() . "' alt='Avatar'>" . $this->nom. "</div>";
    }

    /**
     * @return array[]
     */
    public function getFirstBurntTeam(){
        $brulage = [];
        $almost = [];

        if ($this->brulage[1] == 1) array_push($almost, 1);
        if ($this->brulage[2] == 1) array_push($almost, 2);
        if ($this->brulage[3] == 1) array_push($almost, 3);

        if ($this->brulage[1] >= 2) array_push($brulage, 2, 3, 4);
        else if ($this->brulage[2] >= 2) array_push($brulage, 3, 4);
        else if ($this->brulage[3] >= 2) array_push($brulage, 4);

        return ["almost" => $almost, "burnt" =>$brulage];
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @return int[]
     */
    public function getDisposDepartemental()
    {
        $disposId = [];
        foreach ($this->disposDepartementales as $dispo){
            $disposId[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
        }
        return $disposId;
    }

    /**
     * @param mixed $dispos
     * @return Competiteur
     */
    public function setDisposDepartemental($dispos): self
    {
        $this->disposDepartementales = $dispos;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getDisposParis()
    {
        $disposId = [];
        foreach ($this->disposParis as $dispo){
            $disposId[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
        }
        return $disposId;
    }

    /**
     * @param mixed $dispos
     * @return Competiteur
     */
    public function setDisposParis($dispos): self
    {
        $this->disposParis = $dispos;
        return $this;
    }

}