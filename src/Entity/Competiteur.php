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
     * @ORM\Column(name="licence", type="integer", length=11)
     */
    private $licence;

    /**
     * @ORM\Column(name="classement_officiel", type="integer", length=11)
     */
    private $classement_officiel;

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
     * @var string
     *
     * @Assert\Length(
     *      min = 10,
     *      max = 255,
     *      minMessage = "Votre lien doit contenir au moins {{ limit }} letttres",
     *      maxMessage = "Votre lien doit contenir au maximum {{ limit }} letttres"
     * )
     *
     * @Assert\Url(
     *      message = "The url '{{ value }}' is not a valid url",
     *      protocols = {"http", "https", "ftp"},
     *      relativeProtocol = true
     * )
     *
     * @ORM\Column(type="string", length=255, name="avatar")
     */
    private $avatar;

    /**
     * @ORM\Column(type="json", name="brulageDepartemental")
     */
    private $brulageDepartemental;

    /**
     * @ORM\Column(type="json", name="brulageParis")
     */
    private $brulageParis;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DisponibiliteDepartementale", mappedBy="idCompetiteur")
     */
    private $disposDepartementales;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DisponibiliteParis", mappedBy="idCompetiteur")
     */
    private $disposParis;

    /**
     * @return array
     */
    public function getBrulageDepartemental(): array
    {
        return $this->brulageDepartemental;
    }

    /**
     * @param mixed $brulageDepartemental
     * @return Competiteur
     */
    public function setBrulageDepartemental($brulageDepartemental): self
    {
        $this->brulageDepartemental = $brulageDepartemental;
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
    public function getLicence(): ?int
    {
        return $this->licence;
    }

    /**
     * @param int $licence
     * @return $this
     */
    public function setLicence(int $licence): self
    {
        $this->licence = $licence;
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
     * @return array[]
     */
    public function getFirstBurntTeamDepartemental(){
        $brulageDepartemental = [];
        $almost = [];

        if ($this->brulageDepartemental[1] == 1) array_push($almost, 1);
        if ($this->brulageDepartemental[2] == 1) array_push($almost, 2);
        if ($this->brulageDepartemental[3] == 1) array_push($almost, 3);

        if ($this->brulageDepartemental[1] >= 2) array_push($brulageDepartemental, 2, 3, 4);
        else if ($this->brulageDepartemental[2] >= 2) array_push($brulageDepartemental, 3, 4);
        else if ($this->brulageDepartemental[3] >= 2) array_push($brulageDepartemental, 4);

        return ["almost" => $almost, "burnt" =>$brulageDepartemental];
    }

    /**
     * @return array[]
     */
    public function getFirstBurntTeamParis(){
        $brulageParis = [];
        $almost = [];

        if ($this->brulageParis[1] == 1) array_push($almost, 1);
        if ($this->brulageParis[2] == 1) array_push($almost, 2);

        if ($this->brulageParis[1] >= 2) array_push($brulageParis, 2, 3, 4);
        else if ($this->brulageParis[2] >= 2) array_push($brulageParis, 3, 4);

        return ["almost" => $almost, "burnt" =>$brulageParis];
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param $avatar
     * @return Competiteur
     */
    public function setAvatar($avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrulageParis()
    {
        return $this->brulageParis;
    }

    /**
     * @param mixed $brulageParis
     * @return Competiteur
     */
    public function setBrulageParis($brulageParis): self
    {
        $this->brulageParis = $brulageParis;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClassementOfficiel()
    {
        return $this->classement_officiel;
    }

    /**
     * @param mixed $classement_officiel
     * @return Competiteur
     */
    public function setClassementOfficiel($classement_officiel): self
    {
        $this->classement_officiel = $classement_officiel;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getDisposDepartementales()
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

    /**
     * @return string
     */
    public function getPlayersChips(){
        return "<div class='chip'><img src='" . $this->getAvatar() . "' alt='Avatar'>" . $this->nom. " - " . $this->getClassementOfficiel() . " pts</div>";
    }

}