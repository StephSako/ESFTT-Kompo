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
    const LABEL_CHAMPIONNAT_LABEL = [
        'championnat-departemental' => 'Championnat départemental',
        'criterium-federal' => 'Critérium fédéral',
        'championnat-de-paris' => 'Championnat de Paris'
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
        throw new Exception("Ce championnat n'existe pas", 404);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getFormattedLabel(string $type): string {
        return self::LABEL_CHAMPIONNAT_LABEL[$type];
    }
}