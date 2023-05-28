<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
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
 *
 * @UniqueEntity(
 *     fields={"licence", "username"}
 * )
 *
 * @Vich\Uploadable()
 */
class Competiteur implements UserInterface, Serializable
{
    /**
     * Tableau des catégories d'âge en fonction des dates
     */
    const CATEGORIE_AGE_LABEL = [
        ['libelle' => 'Vétéran 5', 'maxDate' => '/12/31', 'yearMinGap' => 120, 'yearMaxGap' => 80],
        ['libelle' => 'Vétéran 4', 'maxDate' => '/12/31', 'yearMinGap' => 79, 'yearMaxGap' => 70],
        ['libelle' => 'Vétéran 3', 'maxDate' => '/12/31', 'yearMinGap' => 69, 'yearMaxGap' => 60],
        ['libelle' => 'Vétéran 2', 'maxDate' => '/12/31', 'yearMinGap' => 59, 'yearMaxGap' => 50],
        ['libelle' => 'Vétéran 1', 'maxDate' => '/12/31', 'yearMinGap' => 49, 'yearMaxGap' => 40],
        ['libelle' => 'Sénior',    'maxDate' => '/12/31', 'yearMinGap' => 39, 'yearMaxGap' => 18],
        ['libelle' => 'Junior 3',  'maxDate' => '/12/31', 'yearMinGap' => 17, 'yearMaxGap' => 17],
        ['libelle' => 'Junior 2',  'maxDate' => '/12/31', 'yearMinGap' => 16, 'yearMaxGap' => 16],
        ['libelle' => 'Junior 1',  'maxDate' => '/12/31', 'yearMinGap' => 15, 'yearMaxGap' => 15],
        ['libelle' => 'Cadet 2',   'maxDate' => '/12/31', 'yearMinGap' => 14, 'yearMaxGap' => 14],
        ['libelle' => 'Cadet 1',   'maxDate' => '/12/31', 'yearMinGap' => 13, 'yearMaxGap' => 13],
        ['libelle' => 'Minime 2',  'maxDate' => '/12/31', 'yearMinGap' => 12, 'yearMaxGap' => 12],
        ['libelle' => 'Minime 1',  'maxDate' => '/12/31', 'yearMinGap' => 11, 'yearMaxGap' => 11],
        ['libelle' => 'Benjamin 2','maxDate' => '/12/31', 'yearMinGap' => 10, 'yearMaxGap' => 10],
        ['libelle' => 'Benjamin 1', 'maxDate' => '/12/31', 'yearMinGap' => 9, 'yearMaxGap' => 9],
        ['libelle' => 'Poussin',    'maxDate' => '', 'yearMinGap' => 8, 'yearMaxGap' => 0]
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_competiteur")
     */
    private $idCompetiteur;

    /**
     * @var string
     *
     * @Assert\Length(
     *      max = 11,
     *      maxMessage = "La licence doit contenir au maximum {{ limit }} chiffres"
     * )
     *
     * @Assert\Regex(
     *     pattern="/^[0-9]{0,11}$/",
     *     message="La licence doit contenir au maximum 11 chiffres"
     * )
     *
     * @ORM\Column(name="licence", type="string", length=11, nullable=true)
     */
    private $licence;

    /**
     * @var int
     *
     * @Assert\GreaterThanOrEqual(
     *     value = 300,
     *     message = "Le classement doit être supérieur à {{ compared_value }}"
     * )
     *
     * @Assert\LessThanOrEqual(
     *     value = 40000,
     *     message = "Le classement doit être inférieur à {{ compared_value }}"
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
     * @var DateTime|null
     *
     * @ORM\Column(type="date", name="date_naissance", nullable=true)
     */
    private $dateNaissance;

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
    private $password;

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
     *      maxMessage = "L'adresse e-mail doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @Assert\Email(
     *     message = "L'adresse e-mail n'est pas valide"
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
     *      maxMessage = "L'adresse e-mail doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @Assert\Email(
     *     message = "L'adresse e-mail n'est pas valide"
     * )
     */
    private $mail2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number", type="string", length=10, nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/^0[0-9]{9}$/",
     *     message="Le numéro de téléphone doit contenir 10 chiffres et commencer par 0"
     * )
     */
    private $phoneNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number2", type="string", length=10, nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/^0[0-9]{9}$/",
     *     message="Le numéro de téléphone doit contenir 10 chiffres et commencer par 0"
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
     * @ORM\Column(type="boolean", name="is_entraineur", nullable=false)
     */
    private $isEntraineur = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_admin", nullable=false)
     */
    private $isAdmin = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_loisir", nullable=false)
     */
    private $isLoisir = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_crit_fed", nullable=false)
     */
    private $isCritFed = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_competiteur", nullable=false)
     */
    private $isCompetiteur = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_jeune", nullable=false)
     */
    private $isJeune = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_archive", nullable=false)
     */
    private $isArchive = false;

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
     * Permet de rendre les liens d'init/reset password invalides (si encore dans les délais) si le password a déjà été initialisé/reset
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_password_resetting", nullable=false)
     */
    private $isPasswordResetting = false;

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
     *      mimeTypes = {"image/jpg", "image/jpeg", "image/png", "image/gif"},
     *      mimeTypesMessage = "L'image doit être au format JPEG/JPG, PNG ou GIF"
     * )
     *
     * @Vich\UploadableField(mapping="property_image", fileNameProperty="avatar")
     */
    private $imageFile;

    /**
     * @var integer|null
     *
     * @Assert\GreaterThanOrEqual(
     *     value = 2016,
     *     message = "L'année de la sauvegarde du certificat médical doit être supérieur à {{ compared_value }}"
     * )
     *
     * @Assert\LessThanOrEqual(
     *     value = 9999,
     *     message = "L'année de la sauvegarde du certificat médical doit être inférieur à {{ compared_value }}"
     * )
     *
     * @ORM\Column(type="integer", length=4, name="annee_certificat_medical", nullable=true)
     */
    private $anneeCertificatMedical;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Titularisation", mappedBy="idCompetiteur")
     */
    private $titularisations;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", name="updatedAt", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Equipe", inversedBy="joueursAssocies", cascade={"persist"})
     * @ORM\JoinTable(name="prive_titularisation",
                      joinColumns={
                          @ORM\JoinColumn(name="id_competiteur", referencedColumnName="id_competiteur")
                      },
                      inverseJoinColumns={
                          @ORM\JoinColumn(name="id_equipe", referencedColumnName="id_equipe")
                      }
     * )
     */
    private $equipesAssociees;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Disponibilite", mappedBy="idCompetiteur", cascade={"remove"}, orphanRemoval=true)
     */
    private $dispos;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir maximum 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code1", nullable=true)
     */
    private $code1;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code2", nullable=true)
     */
    private $code2;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code3", nullable=true)
     */
    private $code3;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code4", nullable=true)
     */
    private $code4;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code5", nullable=true)
     */
    private $code5;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code6", nullable=true)
     */
    private $code6;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code7", nullable=true)
     */
    private $code7;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code8", nullable=true)
     */
    private $code8;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code9", nullable=true)
     */
    private $code9;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code10", nullable=true)
     */
    private $code10;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code11", nullable=true)
     */
    private $code11;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code12", nullable=true)
     */
    private $code12;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code13", nullable=true)
     */
    private $code13;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 5,
     *      maxMessage = "Le code doit contenir exactement 5 caractères"
     * )
     *
     * @ORM\Column(type="string", length=5, name="code14", nullable=true)
     */
    private $code14;

    /**
     * @return string|null
     */
    public function getCode1(): ?string
    {
        return $this->code1;
    }

    /**
     * @param string|null $code1
     * @return Competiteur
     */
    public function setCode1(?string $code1): self
    {
        $this->code1 = $code1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode2(): ?string
    {
        return $this->code2;
    }

    /**
     * @param string|null $code2
     * @return Competiteur
     */
    public function setCode2(?string $code2): self
    {
        $this->code2 = $code2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode3(): ?string
    {
        return $this->code3;
    }

    /**
     * @param string|null $code3
     * @return Competiteur
     */
    public function setCode3(?string $code3): self
    {
        $this->code3 = $code3;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode4(): ?string
    {
        return $this->code4;
    }

    /**
     * @param string|null $code4
     * @return Competiteur
     */
    public function setCode4(?string $code4): self
    {
        $this->code4 = $code4;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode5(): ?string
    {
        return $this->code5;
    }

    /**
     * @param string|null $code5
     * @return Competiteur
     */
    public function setCode5(?string $code5): self
    {
        $this->code5 = $code5;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode6(): ?string
    {
        return $this->code6;
    }

    /**
     * @param string|null $code6
     * @return Competiteur
     */
    public function setCode6(?string $code6): self
    {
        $this->code6 = $code6;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode7(): ?string
    {
        return $this->code7;
    }

    /**
     * @param string|null $code7
     * @return Competiteur
     */
    public function setCode7(?string $code7): self
    {
        $this->code7 = $code7;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode8(): ?string
    {
        return $this->code8;
    }

    /**
     * @param string|null $code8
     * @return Competiteur
     */
    public function setCode8(?string $code8): self
    {
        $this->code8 = $code8;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode9(): ?string
    {
        return $this->code9;
    }

    /**
     * @param string|null $code9
     * @return Competiteur
     */
    public function setCode9(?string $code9): self
    {
        $this->code9 = $code9;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode10(): ?string
    {
        return $this->code10;
    }

    /**
     * @param string|null $code10
     * @return Competiteur
     */
    public function setCode10(?string $code10): self
    {
        $this->code10 = $code10;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode11(): ?string
    {
        return $this->code11;
    }

    /**
     * @param string|null $code11
     * @return Competiteur
     */
    public function setCode11(?string $code11): self
    {
        $this->code11 = $code11;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode12(): ?string
    {
        return $this->code12;
    }

    /**
     * @param string|null $code12
     * @return Competiteur
     */
    public function setCode12(?string $code12): self
    {
        $this->code12 = $code12;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode13(): ?string
    {
        return $this->code13;
    }

    /**
     * @param string|null $code13
     * @return Competiteur
     */
    public function setCode13(?string $code13): self
    {
        $this->code13 = $code13;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode14(): ?string
    {
        return $this->code14;
    }

    /**
     * @param string|null $code14
     * @return Competiteur
     */
    public function setCode14(?string $code14): self
    {
        $this->code14 = $code14;
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
     * @return string|null
     */
    public function getLicence(): ?string
    {
        return $this->licence;
    }

    /**
     * @param string|null $licence
     * @return $this
     */
    public function setLicence(?string $licence): self
    {
        $this->licence = strlen(trim($licence)) > 0 ? trim($licence) : null;
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
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     * @return $this
     */
    public function setNom(?string $nom): self
    {
        $this->nom = mb_convert_case(trim($nom), MB_CASE_UPPER, "UTF-8");
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
        $roles = [];
        if (!$this->isArchive()){
            if ($this->isEntraineur()) $roles[] = 'ROLE_ENTRAINEUR';
            if ($this->isAdmin()) $roles[] = 'ROLE_ADMIN';
            if ($this->isLoisir())  $roles[] = 'ROLE_LOISIR';
            if ($this->isJeune())  $roles[] = 'ROLE_JEUNE';
            if ($this->isCapitaine()) $roles[] = 'ROLE_CAPITAINE';
            if ($this->isCompetiteur()) $roles[] = 'ROLE_COMPETITEUR';
        } else $roles[] = 'ROLE_ARCHIVE';

        return $roles;
    }

    public function getRolesFormatted() {
        $formattedRoles = str_replace('Role_', '', implode(', ', array_map(function($role){
            return mb_convert_case($role, MB_CASE_TITLE, "UTF-8");
        }, $this->getRoles())));
        return strrpos($formattedRoles, ',') ? substr_replace($formattedRoles, ' et', strrpos($formattedRoles, ','), 1) : $formattedRoles;
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
        $this->username = (trim($username) ?: 'username');
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
     * @param $data
     */
    public function unserialize($data)
    {
        list(
            $this->idCompetiteur,
            $this->username,
            $this->password
            ) = unserialize($data, ['allowed_classes' => false]);
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
    public function getDispos(): array
    {
        $disposId = [];
        foreach ($this->dispos as $dispo){
            $disposId[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
        }
        return $disposId;
    }

    /**
     * @param mixed|null $dispos
     * @return Competiteur
     */
    public function setDispos($dispos): self
    {
        $this->dispos = $dispos;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelect(): string
    {
        return $this->getNom() . ' ' . $this->getPrenom();
    }

    /**
     * @param bool $loisir
     * @return Competiteur
     */
    public function setIsLoisir(bool $loisir): self
    {
        $this->isLoisir = $loisir;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLoisir(): bool
    {
        return $this->isLoisir;
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
        $this->mail = strlen(trim($mail)) > 0 ? trim($mail) : null;
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
        $this->mail2 = strlen(trim($mail2)) > 0 ? trim($mail2) : null;
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
        $this->phoneNumber = trim($phoneNumber);
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
        $this->phoneNumber2 = trim($phoneNumber2);
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
        if ($this->imageFile instanceof UploadedFile || $imageFile == null) {
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
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $prenom
     * @return Competiteur
     */
    public function setPrenom(?string $prenom): self
    {
        $this->prenom = mb_convert_case(trim($prenom), MB_CASE_TITLE, "UTF-8");
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAnneeCertificatMedical(): ?int
    {
        return $this->anneeCertificatMedical;
    }

    /**
     * @return Competiteur
     */
    public function renouvelerAnneeCertificatMedical(): self
    {
        $this->anneeCertificatMedical = intval((new DateTime())->format('Y'));
        return $this;
    }

    /**
     * @param int|null $annee
     * @return Competiteur
     */
    public function setAnneeCertificatMedical(?int $annee): self
    {
        $this->anneeCertificatMedical = $annee;
        return $this;
    }

    public function getLabelCertificatRentree(): string {
        return ($this->getAnneeCertificatMedical() != null ? ($this->getAnneeCertificatMedical()+3) . '/' . ($this->getAnneeCertificatMedical()+4) : (new DateTime())->format('Y') . '-' . (intval((new DateTime())->format('Y'))+1));
    }

    /**
     * @return string[]
     */
    public function isCertifMedicalInvalid(): array {
        if (((new DateTime())->format('n') >= 6 || $this->getAnneeCertificatMedical() < (new DateTime())->format('Y')-3) && (($this->getAge() == null || $this->getAge() >= 18) &&
            ($this->getAnneeCertificatMedical() == null || $this->getAnneeCertificatMedical() < (new DateTime())->format('Y')-2)))
            return [
                'status' => true,
                'message' => 'Votre certificat médical est à renouveler pour la rentrée <b>' . ($this->getAnneeCertificatMedical() != null ? ($this->getAnneeCertificatMedical()+3) . '/' . ($this->getAnneeCertificatMedical()+4) : (new DateTime())->format('Y') . '/' . (intval((new DateTime())->format('Y'))+1)) . '</b>',
                'shortMessage' => $this->getLabelCertificatRentree()
            ];
        return [
            'status' => false,
            'message' => null,
            'shortMessage' => $this->getLabelCertificatRentree()
        ];
    }

    /**
     * Retourne les informations serializées pour l'export en PDF
     * @return array
     * @throws Exception
     */
    public function serializeToPDF(): array {
        return [
            $this->getLicence(),
            $this->getNom(),
            $this->getPrenom(),
            $this->getDateNaissance() ? $this->getDateNaissance()->format('d/m/Y') : null,
            $this->getClassementOfficiel(),
            $this->getClassementOfficiel() ? intval($this->getClassementOfficiel()/100) : null,
            $this->isCritFed() ? 'Oui' : 'Non',
            $this->getCategorieAgeLabel(),
            $this->getAnneeCertificatMedical(),
            $this->getMail(),
            $this->getMail2(),
            $this->getPhoneNumber(),
            $this->getPhoneNumber2(),
            $this->getRolesFormatted()
        ];
    }

    /**
     * @return bool
     */
    public function isEntraineur(): bool
    {
        return $this->isEntraineur;
    }

    /**
     * @param bool $isEntraineur
     * @return Competiteur
     */
    public function setIsEntraineur(bool $isEntraineur): self
    {
        $this->isEntraineur = $isEntraineur;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArchive(): bool
    {
        return $this->isArchive;
    }

    /**
     * @param bool $isArchive
     * @return Competiteur
     */
    public function setIsArchive(bool $isArchive): self
    {
        $this->isArchive = $isArchive;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCritFed(): bool
    {
        return $this->isCritFed;
    }

    /**
     * @param bool $isCritFed
     * @return Competiteur
     */
    public function setIsCritFed(bool $isCritFed): self
    {
        $this->isCritFed = $isCritFed;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateNaissance(): ?DateTime
    {
        return $this->dateNaissance;
    }

    /**
     * @param DateTime|null $dateNaissance
     * @return Competiteur
     */
    public function setDateNaissance(?DateTime $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAge(): ?int
    {
        if ($this->getDateNaissance()) {
            $now = new DateTime();
            $interval = $now->diff($this->getDateNaissance());
            return $interval->y;
        } else return null;
    }

    /**
     * @return string|null
     */
    public function getFirstContactableMail(): ?string {
        if ($this->getMail() && $this->isContactableMail()) return $this->getMail();
        if ($this->getMail2() && $this->isContactableMail2()) return $this->getMail2();
        return null;
    }

    /**
     * @return string|null
     */
    public function getFirstContactablePhoneNumber(): ?string {
        if ($this->getPhoneNumber() && $this->isContactablePhoneNumber()) return $this->getPhoneNumber();
        if ($this->getPhoneNumber2() && $this->isContactablePhoneNumber2()) return $this->getPhoneNumber2();
        return null;
    }

    /**
     * @return bool
     */
    public function isCompetiteur(): bool
    {
        return $this->isCompetiteur;
    }

    /**
     * @param bool $isCompetiteur
     * @return Competiteur
     */
    public function setIsCompetiteur(bool $isCompetiteur): self
    {
        $this->isCompetiteur = $isCompetiteur;
        return $this;
    }

    /**
     * @return bool
     */
    public function isJeune(): bool
    {
        return $this->isJeune;
    }

    /**
     * @param bool $isJeune
     * @return Competiteur
     */
    public function setIsJeune(bool $isJeune): Competiteur
    {
        $this->isJeune = $isJeune;
        return $this;
    }

    /**
     * Renvoie la version longue de la catégorie d'âge
     * @return string|null
     * @throws Exception
     */
    public function getCategorieAgeLabel(): ?string {
        $gap = date('m') < 7 ? 1 : 0;
        $categorie = array_values(array_filter(self::CATEGORIE_AGE_LABEL, function($categorieRef) use($gap) {
            $minYear = date('Y') - $categorieRef['yearMinGap'] - $gap;
            $maxYear = date('Y') - $categorieRef['yearMaxGap'] - $gap;
            return new DateTime($minYear . '/01/01') <= $this->getDateNaissance() && new DateTime($maxYear . $categorieRef['maxDate']) >= $this->getDateNaissance();
        }));
        return count($categorie) ? $categorie[0]['libelle'] : null;
    }

    /**
     * Déterminer si le joueur est sélectionné dans une des compositions d'équipe d'une rencontre
     * @param Rencontre[] $compos
     * @return int|null
     */
    public function isSelectedIn(array $compos): ?int {
        $selectionArray = array_values(array_filter(array_map(function($compo) {
            return in_array($this->getIdCompetiteur(), $compo->getSelectedPlayers()) ? $compo : null;
        }, $compos), function($compoFiltree){
            return $compoFiltree != null;
        }));

        return count($selectionArray) ? $selectionArray[0]->getIdEquipe()->getNumero() : null;
    }

    /**
     * Retourne les champs incomplétés du profil d'un joueur
     * @return array
     */
    public function profileCompletion(): array
    {
        $champsManquants = [];
        $completude = 0;

        foreach (
            [
                $this->getNom(), $this->getPrenom(), $this->getUsername(), $this->getDateNaissance(),
                !$this->isCertifMedicalInvalid()['status'], $this->getFirstContactableMail(), $this->getFirstContactablePhoneNumber(),
                $this->getLicence(), $this->getAvatar()
            ] as $index => $field) {
            if (!$field){
                switch ($index) {
                    case 0:
                        $champsManquants[] = 'Nom';
                        break;
                    case 1:
                        $champsManquants[] = 'Prénom';
                        break;
                    case 2:
                        $champsManquants[] = 'Pseudo';
                        break;
                    case 3:
                        $champsManquants[] = 'Date de naissance';
                        break;
                    case 4:
                        $champsManquants[] = 'Année du certificat médical';
                        break;
                    case 5:
                        $champsManquants[] = 'Une adresse e-mail contactable';
                        break;
                    case 6:
                        $champsManquants[] = 'Un numéro de téléphone contactable';
                        break;
                    case 7:
                        $champsManquants[] = 'Licence';
                        break;
                    case 8:
                        $champsManquants[] = 'Photo de profil';
                        break;
                }
                $completude++;
            }
        }

        return [
            "champsManquants" => $champsManquants,
            "completude" => round(((9 - $completude) * 100) / 9)
        ];
    }

    /**
     * @return bool
     */
    public function isPasswordResetting(): bool
    {
        return $this->isPasswordResetting;
    }

    /**
     * @param bool $isPasswordResetting
     * @return Competiteur
     */
    public function setIsPasswordResetting(bool $isPasswordResetting): self
    {
        $this->isPasswordResetting = $isPasswordResetting;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEquipesAssociees(): Collection
    {
        return $this->equipesAssociees ?? new ArrayCollection([]);
    }

    /**
     * @param Championnat[] $championnats
     * @return int[]
     */
    public function getTableEquipesAssociees(array $championnats): array
    {
        $equipesAssociees = [];
        foreach ($championnats as $champ) {
            $titusChampJoueur = array_filter($champ->getTitularisations()->toArray(), function($titu){
                return $titu->getIdCompetiteur()->getIdCompetiteur() == $this->getIdCompetiteur();
            });
            $equipeTitu = array_shift($titusChampJoueur);
            $equipesAssociees[$champ->getNom()] = $equipeTitu ? $equipeTitu->getIdEquipe()->getNumero() : null;
        }
        return $equipesAssociees;
    }

    /**
     * @param Collection $equipesAssociees
     * @return Competiteur
     */
    public function setEquipesAssociees(Collection $equipesAssociees): self
    {
        $this->equipesAssociees = $equipesAssociees;
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
     * @return Competiteur
     */
    public function setTitularisations(Collection $titularisations): self
    {
        $this->titularisations = $titularisations;
        return $this;
    }
}