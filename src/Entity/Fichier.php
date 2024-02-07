<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FichierRepository")
 * @ORM\Table(
 *     name="prive_fichier",
 *     indexes={
 *         @Index(name="IDX_setting", columns={"id_setting"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_nom_fichier", columns={"nom_fichier"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"nom_fichier"}
 * )
 */
class Fichier
{
    /**
     * @var Settings
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Settings", targetEntity="App\Entity\Settings")
     * @ORM\JoinColumn(name="id_setting", nullable=false, referencedColumnName="id")
     */
    protected $setting;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_fichier", nullable=false)
     */
    private $idFichier;
    /**
     * @var string
     *
     * @Assert\Length(
     *      min  = 1,
     *      max  = 100,
     *      minMessage = "Le nom du fichier doit contenir au minimum {{ limit }} lettres",
     *      maxMessage = "Le nom du fichier doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="string", name="nom_fichier", nullable=false, length=100)
     */
    private $nomFichier;

    /**
     * @param Settings $setting
     * @param string $nom
     */
    public function __construct(Settings $setting, string $nom)
    {
        $this->setting = $setting;
        $this->nomFichier = $nom;
    }

    /**
     * @return Settings
     */
    public function getSetting(): Settings
    {
        return $this->setting;
    }

    /**
     * @param Settings $setting
     * @return Fichier
     */
    public function setSetting(Settings $setting): self
    {
        $this->setting = $setting;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdFichier()
    {
        return $this->idFichier;
    }

    /**
     * @param mixed $idFichier
     * @return Fichier
     */
    public function setIdFichier($idFichier): self
    {
        $this->idFichier = $idFichier;
        return $this;
    }

    /**
     * @return string
     */
    public function getNomFichier(): string
    {
        return $this->nomFichier;
    }

    /**
     * @param string $nomFichier
     * @return Fichier
     */
    public function setNomFichier(string $nomFichier): self
    {
        $this->nomFichier = $nomFichier;
        return $this;
    }
}