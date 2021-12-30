<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingsRepository")
 * @ORM\Table(
 *     name="prive_settings"
 * )
 */
class Settings
{
    const LABELS_DATA_TYPE = [
        'championnat-departemental' => 'Championnat départemental',
        'criterium-federal' => 'Critérium fédéral',
        'championnat-de-paris' => 'Championnat de Paris',
        'mail-bienvenue' => 'Mail de bienvenue',
        'mail-mdp-oublie' => 'Mail de mot de passe oublié'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="infos_championnat_departemental", nullable=true)
     */
    private $infosChampionnatDepartemental;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="infos_criterium_federal", nullable=true)
     */
    private $infosCriteriumFederal;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="infos_championnat_de_paris", nullable=true)
     */
    private $infosChampionnatDeParis;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="mail_bienvenue", nullable=true)
     */
    private $mailBienvenue;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="mail_mdp_oublie", nullable=true)
     */
    private $mailMdpOublie;

    /**
     * @return string|null
     */
    public function getMailBienvenue(): ?string
    {
        return $this->mailBienvenue;
    }

    /**
     * @param string|null $mailBievenue
     * @return Settings
     */
    public function setMailBienvenue(?string $mailBievenue): self
    {
        $this->mailBienvenue = $mailBievenue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMailMdpOublie(): ?string
    {
        return $this->mailMdpOublie;
    }

    /**
     * @param string|null $mailMdpOublie
     * @return Settings
     */
    public function setMailMdpOublie(?string $mailMdpOublie): self
    {
        $this->mailMdpOublie = $mailMdpOublie;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Settings
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInfosChampionnatDepartemental(): ?string
    {
        return $this->infosChampionnatDepartemental;
    }

    /**
     * @param string|null $infosChampionnatDepartemental
     * @return Settings
     */
    public function setInfosChampionnatDepartemental(?string $infosChampionnatDepartemental): self
    {
        $this->infosChampionnatDepartemental = $infosChampionnatDepartemental;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInfosChampionnatDeParis(): ?string
    {
        return $this->infosChampionnatDeParis;
    }

    /**
     * @param string|null $infosChampionnatDeParis
     * @return Settings
     */
    public function setInfosChampionnatDeParis(?string $infosChampionnatDeParis): self
    {
        $this->infosChampionnatDeParis = $infosChampionnatDeParis;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInfosCriteriumFederal(): ?string
    {
        return $this->infosCriteriumFederal;
    }

    /**
     * @param string|null $infosCriteriumFederal
     * @return Settings
     */
    public function setInfosCriteriumFederal(?string $infosCriteriumFederal): self
    {
        $this->infosCriteriumFederal= $infosCriteriumFederal;
        return $this;
    }

    /**
     * @param string $type
     * @return string|null
     * @throws Exception
     */
    public function getInfosType(string $type): ?string
    {
        if ($type == 'championnat-departemental') return $this->getInfosChampionnatDepartemental();
        else if ($type == 'criterium-federal') return $this->getInfosCriteriumFederal();
        else if ($type == 'championnat-de-paris') return $this->getInfosChampionnatDeParis();
        else if ($type == 'mail-bienvenue') return $this->getMailBienvenue();
        else if ($type == 'mail-mdp-oublie') return $this->getMailMdpOublie();
        throw new Exception("Ce championnat n'existe pas", 404);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getFormattedLabel(string $type): string {
        return self::LABELS_DATA_TYPE[$type];
    }
}