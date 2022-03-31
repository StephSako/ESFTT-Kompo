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
        'gymnase' => ' sur le gymnase',
        'mail-bienvenue' => 'mail de bienvenue',
        'mail-mdp-oublie' => 'mail de mot de passe oublié',
        'mail-certif-medic-perim' => 'mail de certificat médical périmé',
        'mail-pre-phase' => 'mail de pré-phase'
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
     * @ORM\Column(type="text", name="infos_gymnase", nullable=true)
     */
    private $infosGymnase;

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
     * @var string|null
     *
     * @ORM\Column(type="text", name="mail_certif_medic_perim", nullable=true)
     */
    private $mailCertifMedicPerim;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="mail_pre_phase", nullable=true)
     */
    private $mailPrePhase;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="message_sans_dispo", nullable=true)
     */
    private $messageSansDispo;

    /**
     * @return string|null
     */
    public function getMailCertifMedicPerim(): ?string
    {
        return $this->mailCertifMedicPerim;
    }

    /**
     * @param string|null $mailCertifMedicPerim
     * @return Settings
     */
    public function setMailCertifMedicPerim(?string $mailCertifMedicPerim): self
    {
        $this->mailCertifMedicPerim = $mailCertifMedicPerim;
        return $this;
    }

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
    public function getInfosGymnase(): ?string
    {
        return $this->infosGymnase;
    }

    /**
     * @param string|null $infosGymnase
     * @return Settings
     */
    public function setInfosGymnase(?string $infosGymnase): self
    {
        $this->infosGymnase = $infosGymnase;
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
     * @return string|null
     */
    public function getMailPrePhase(): ?string
    {
        return $this->mailPrePhase;
    }

    /**
     * @param string|null $mailPrePhase
     * @return Settings
     */
    public function setMailPrePhase(?string $mailPrePhase): self
    {
        $this->mailPrePhase = $mailPrePhase;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessageSansDispo(): ?string
    {
        return $this->messageSansDispo;
    }

    /**
     * @param string|null $messageSansDispo
     * @return Settings
     */
    public function setMessageSansDispo(?string $messageSansDispo): self
    {
        $this->messageSansDispo = $messageSansDispo;
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
        else if ($type == 'gymnase') return $this->getInfosGymnase();
        else if ($type == 'mail-bienvenue') return $this->getMailBienvenue();
        else if ($type == 'mail-mdp-oublie') return $this->getMailMdpOublie();
        else if ($type == 'mail-certif-medic-perim') return $this->getMailCertifMedicPerim();
        else if ($type == 'mail-pre-phase') return $this->getMailPrePhase();
        else if ($type == 'sans-dispo') return $this->getMessageSansDispo();
        throw new Exception("Ce contenu n'existe pas", 404);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getFormattedLabel(string $type): string {
        return self::LABELS_DATA_TYPE[$type];
    }
}