<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="text", name="informations_competition", nullable=false)
     */
    private $informations_competition;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="informations_criterium", nullable=false)
     */
    private $informations_criterium;

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
    public function getInformationsCompetition(): string
    {
        return $this->informations_competition;
    }

    /**
     * @param string|null $informations_competition
     * @return Settings
     */
    public function setInformationsCompetition(?string $informations_competition = ''): self
    {
        $informations_competition = $informations_competition ?: '';
        $this->informations_competition = $informations_competition;
        return $this;
    }

    /**
     * @return string
     */
    public function getInformationsCriterium(): string
    {
        return $this->informations_criterium;
    }

    /**
     * @param string|null $informations_criterium
     * @return Settings
     */
    public function setInformationsCriterium(?string $informations_criterium): self
    {
        $informations_criterium = $informations_criterium ?: '';
        $this->informations_criterium = $informations_criterium;
        return $this;
    }

    /**
     * @param string $type
     * @return string
     * @throws Exception
     */
    public function getInformations(string $type): string
    {
        if ($type == 'competition') return $this->getInformationsCompetition();
        else if ($type == 'criterium') return $this->getInformationsCriterium();
        throw new Exception('Page inexistante', 404);
    }
}