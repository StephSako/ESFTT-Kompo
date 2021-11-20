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
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="informations_competition_departementale", nullable=false)
     */
    private $informationsCompetitionDepartementale;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="informations_criterium_federal", nullable=false)
     */
    private $informationsCriteriumFederal;

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
     * @return string
     */
    public function getInformationsCompetitionDepartementale(): string
    {
        return $this->informationsCompetitionDepartementale;
    }

    /**
     * @param string|null $informationsCompetitionDepartementale
     * @return Settings
     */
    public function setInformationsCompetitionDepartementale(?string $informationsCompetitionDepartementale = ''): self
    {
        $informationsCompetitionDepartementale = $informationsCompetitionDepartementale ?: '';
        $this->informationsCompetitionDepartementale = $informationsCompetitionDepartementale;
        return $this;
    }

    /**
     * @return string
     */
    public function getInformationsCriteriumFederal(): string
    {
        return $this->informationsCriteriumFederal;
    }

    /**
     * @param string|null $informationsCriteriumFederal
     * @return Settings
     */
    public function setInformationsCriteriumFederal(?string $informationsCriteriumFederal): self
    {
        $informationsCriteriumFederal = $informationsCriteriumFederal ?: '';
        $this->informationsCriteriumFederal= $informationsCriteriumFederal;
        return $this;
    }

    /**
     * @param string $type
     * @return string
     * @throws Exception
     */
    public function getInformations(string $type): string
    {
        if ($type == 'compétition-départementale') return $this->getInformationsCompetitionDepartementale();
        else if ($type == 'critérium-fédéral') return $this->getInformationsCriteriumFederal();
        throw new Exception('Page inexistante', 404);
    }

    /**
     * @param string $separator
     * @param string $value
     * @return string
     */
    public function getFormattedLabel(string $separator, string $value): string {
        return join($separator, array_map(function ($typeItem) {
            return mb_convert_case($typeItem, MB_CASE_TITLE, "UTF-8");
        }, explode('-', $value)));
    }
}