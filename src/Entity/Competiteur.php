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
     * @Assert\Length(
     *      min = 5,
     *      max = 8,
     *      minMessage = "La licence doit contenir au moins {{ limit }} chiffres",
     *      maxMessage = "La licence doit contenir au maximum {{ limit }} chiffres"
     * )
     *
     * @ORM\Column(name="licence", type="integer", length=8, nullable=false)
     */
    private $licence;

    /**
     * @Assert\GreaterThanOrEqual(500)
     *
     * @ORM\Column(name="classement_officiel", type="integer", length=4, nullable=false, options={"default":500})
     */
    private $classement_officiel = 500;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(type="string", length=100, name="nom", nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100, nullable=false, options={"default":"$2y$12$sOKw0xGfJpYyRRamBiT8kO5qZx7SllVHQ6DEas0S48JbGUxSW7nqC"} )
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 100,
     *      minMessage = "Le mot de passe doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le mot de passe doit contenir au maximum {{ limit }} caractères"
     * )
     */
    private $password = '$2y$12$sOKw0xGfJpYyRRamBiT8kO5qZx7SllVHQ6DEas0S48JbGUxSW7nqC';

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50, nullable=false)
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le pseudo doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le pseudo doit contenir au maximum {{ limit }} caractères"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="boolean", name="is_capitaine", nullable=false)
     */
    private $isCapitaine = false;

    /**
     * @var string
     *
     * @Assert\Url(
     *      message = "Cet URL n'est pas valide",
     *      protocols = {"http", "https", "ftp"},
     *      relativeProtocol = true
     * )
     *
     * @ORM\Column(type="string", length=255, name="avatar", nullable=false, options={"default":"https://cdn1.iconfinder.com/data/icons/ui-next-2020-shopping-and-e-commerce-1/12/75_user-circle-512.png"})
     */
    private $avatar = 'https://cdn1.iconfinder.com/data/icons/ui-next-2020-shopping-and-e-commerce-1/12/75_user-circle-512.png';

    /**
     * @ORM\Column(type="json", name="brulageDepartemental", nullable=false, options={"default":"{'1':0,'2':0,'3':0,'4':0}"})
     */
    private $brulageDepartemental = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

    /**
     * @ORM\Column(type="json", name="brulageParis", nullable=false, options={"default":"{'1':0,'2':0}"})
     */
    private $brulageParis = [1 => 0, 2 => 0];

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
     * @return int
     */
    public function getLicence(): int
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
     * @return string
     */
    public function getNom(): string
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
     * @return bool
     */
    public function getRole(): bool
    {
        return $this->isCapitaine;
    }

    /**
     * @param bool $isCapitaine
     * @return $this
     */
    public function setRole(bool $isCapitaine): self
    {
        $this->isCapitaine = $isCapitaine;
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
        if ($this->getRole()) {
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
     * @return string
     */
    public function getAvatar(): string
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
     * @return array
     */
    public function getBrulageParis(): array
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
     * @return int
     */
    public function getClassementOfficiel(): int
    {
        return $this->classement_officiel;
    }

    /**
     * @param int $classement_officiel
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
     * @param mixed|null $dispos
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
     * @param mixed|null $dispos
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
    public function getSelect(): string
    {
        return $this->nom. " - " . $this->getClassementOfficiel() . " pts";
    }
}