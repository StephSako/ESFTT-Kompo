<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompetiteurRepository")
 * @ORM\Table(
 *     name="prive_competiteur",
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_comp_licence", columns={"licence"}),
 *          @UniqueConstraint(name="UNIQ_comp_username", columns={"username"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"licence", "username"}
 * )
 * @Vich\Uploadable()
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
     * @var int
     *
     * @Assert\Length(
     *      max = 11,
     *      maxMessage = "La licence doit contenir au maximum {{ limit }} chiffres"
     * )
     *
     * @ORM\Column(name="licence", type="integer", length=11, nullable=true)
     */
    private $licence;

    /**
     * @var int
     *
     * @Assert\GreaterThanOrEqual(
     *     value = 500,
     *     message = "Le numéro d'équipe doit être supérieur à {{ value }}"
     * )
     *
     * @Assert\LessThanOrEqual(
     *     value = 20000,
     *     message = "Le numéro d'équipe doit être inférieur à {{ value }}"
     * )
     *
     * @ORM\Column(name="classement_officiel", type="integer", nullable=true)
     */
    private $classement_officiel;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(type="string", length=50, name="nom", nullable=false)
     */
    private $nom;

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
     * @ORM\Column(type="string", length=50, name="prenom", nullable=false)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 100,
     *      minMessage = "Le mot de passe doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le mot de passe doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     */
    private $password = '$2y$12$sOKw0xGfJpYyRRamBiT8kO5qZx7SllVHQ6DEas0S48JbGUxSW7nqC';

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50, nullable=false)
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
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
     * @var string|null
     *
     * @ORM\Column(name="mail", type="string", length=100, nullable=true)
     *
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "L'adresse email doit contenir au maximum {{ limit }} caractères."
     * )
     *
     * @Assert\Email(
     *     message = "L'adresse email '{{ value }}' n'est pas valide."
     * )
     */
    private $mail;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mail2", type="string", length=100, nullable=true)
     *
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "L'adresse email doit contenir au maximum {{ limit }} caractères."
     * )
     *
     * @Assert\Email(
     *     message = "L'adresse email '{{ value }}' n'est pas valide."
     * )
     */
    private $mail2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number", type="string", length=10, nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/[0-9]{10}/"
     * )
     *
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Le numéro de téléphone doit contenir exactement {{ limit }} chiffres."
     * )
     */
    private $phoneNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number2", type="string", length=10, nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/[0-9]{10}/"
     * )
     *
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Le numéro de téléphone doit contenir exactement {{ limit }} chiffres."
     * )
     */
    private $phoneNumber2;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_capitaine", nullable=false)
     */
    private $isCapitaine = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_admin", nullable=false)
     */
    private $isAdmin = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="visitor", nullable=false)
     */
    private $visitor = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="contactable_mail", nullable=false)
     */
    private $contactableMail = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="contactable_mail2", nullable=false)
     */
    private $contactableMail2 = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="contactable_phone_number", nullable=false)
     */
    private $contactablePhoneNumber = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="contactable_phone_number2", nullable=false)
     */
    private $contactablePhoneNumber2 = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, name="avatar", nullable=true)
     */
    private $avatar;

    /**
     * @var File|null
     *
     * @Assert\Image(
     *      mimeTypes = {"image/jpeg", "image/png", "image/gif"},
     *      mimeTypesMessage = "L'image doit être au format .jpeg, .png ou .gif."
     * )
     *
     * @Vich\UploadableField(mapping="property_image", fileNameProperty="avatar")
     */
    private $imageFile;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", name="updatedAt", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="Disponibilite.php", mappedBy="idCompetiteur", cascade={"remove"}, orphanRemoval=true)
     */
    private $disposDepartementale;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DisponibiliteParis", mappedBy="idCompetiteur", cascade={"remove"}, orphanRemoval=true)
     */
    private $disposParis;

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
     * @param int|null $licence
     * @return $this
     */
    public function setLicence(?int $licence): self
    {
        $this->licence = $licence;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @param bool $isAdmin
     * @return Competiteur
     */
    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;
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
     * @return $this
     */
    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
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
     * @return string[]
     */
    public function getRoles(): array
    {
        if (!$this->isVisitor()){
            if ($this->isAdmin()) {
                return ['ROLE_ADMIN', 'ROLE_CAPITAINE', 'ROLE_JOUEUR'];
            } else if ($this->isCapitaine()) {
                return ['ROLE_CAPITAINE', 'ROLE_JOUEUR'];
            } else {
                return ['ROLE_JOUEUR'];
            }
        }
        else return ['ROLE_VISITEUR'];
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     * @return Competiteur
     */
    public function setUsername(?string $username): self
    {
        $this->username = ($username ?: 'username');
        return $this;
    }

    public function eraseCredentials()
    { }

    /**
     * @return string|null
     */
    public function serialize(): ?string
    {
        return serialize([
            $this->idCompetiteur,
            $this->username,
            $this->password
        ]);
    }

    /**
     * @param $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->idCompetiteur,
            $this->username,
            $this->password
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @return int|null
     */
    public function getClassementOfficiel(): ?int
    {
        return $this->classement_officiel;
    }

    /**
     * @param int|null $classement_officiel
     * @return Competiteur
     */
    public function setClassementOfficiel(?int $classement_officiel): self
    {
        $this->classement_officiel = $classement_officiel;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getDisposDepartementale(): array
    {
        $disposId = [];
        foreach ($this->disposDepartementale as $dispo){
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
        $this->disposDepartementale = $dispos;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getDisposParis(): array
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
        return $this->getNom() . ' ' . $this->getPrenom() . ' - ' . $this->getClassementOfficiel() . ' pts';
    }

    /**
     * @param bool $visitor
     * @return Competiteur
     */
    public function setVisitor(bool $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisitor(): bool
    {
        return $this->visitor;
    }

    /**
     * @return string|null
     */
    public function getMail(): ?string
    {
        return $this->mail;
    }

    /**
     * @param string|null $mail
     * @return Competiteur
     */
    public function setMail(?string $mail): self
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMail2(): ?string
    {
        return $this->mail2;
    }

    /**
     * @param string|null $mail2
     * @return Competiteur
     */
    public function setMail2(?string $mail2): self
    {
        $this->mail2 = $mail2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     * @return Competiteur
     */
    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber2(): ?string
    {
        return $this->phoneNumber2;
    }

    /**
     * @param string|null $phoneNumber2
     * @return Competiteur
     */
    public function setPhoneNumber2(?string $phoneNumber2): self
    {
        $this->phoneNumber2 = $phoneNumber2;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCapitaine(): bool
    {
        return $this->isCapitaine;
    }

    /**
     * @param bool $isCapitaine
     * @return Competiteur
     */
    public function setIsCapitaine(bool $isCapitaine): self
    {
        $this->isCapitaine = $isCapitaine;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContactableMail(): bool
    {
        return $this->contactableMail;
    }

    /**
     * @param bool $contactableMail
     * @return Competiteur
     */
    public function setContactableMail(bool $contactableMail): self
    {
        $this->contactableMail = $contactableMail;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContactableMail2(): bool
    {
        return $this->contactableMail2;
    }

    /**
     * @param bool $contactableMail2
     * @return Competiteur
     */
    public function setContactableMail2(bool $contactableMail2): self
    {
        $this->contactableMail2 = $contactableMail2;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContactablePhoneNumber(): bool
    {
        return $this->contactablePhoneNumber;
    }

    /**
     * @param bool $contactablePhoneNumber
     * @return Competiteur
     */
    public function setContactablePhoneNumber(bool $contactablePhoneNumber): self
    {
        $this->contactablePhoneNumber = $contactablePhoneNumber;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContactablePhoneNumber2(): bool
    {
        return $this->contactablePhoneNumber2;
    }

    /**
     * @param bool $contactablePhoneNumber2
     * @return Competiteur
     */
    public function setContactablePhoneNumber2(bool $contactablePhoneNumber2): self
    {
        $this->contactablePhoneNumber2 = $contactablePhoneNumber2;
        return $this;
    }

    /**
     * @param File|null $imageFile
     * @return Competiteur
     */
    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        if ($this->imageFile instanceof UploadedFile) {
            $this->setUpdatedAt(new DateTime('now'));
        }
        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     * @return Competiteur
     */
    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * @param string|null $avatar
     * @return Competiteur
     */
    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom(): string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $prenom
     * @return Competiteur
     */
    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }
}