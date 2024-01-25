<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
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
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(type="text", name="id", nullable=false)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="display_table_role", nullable=true)
     */
    private $displayTableRole;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="title", nullable=false)
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Le titre doit contenir au maximum {{ limit }} caractères"
     * )
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="content", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="label", nullable=false)
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="type", nullable=false)
     */
    private $type;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Settings
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayTableRole(): ?string
    {
        return $this->displayTableRole;
    }

    /**
     * @param string|null $displayTableRole
     * @return Settings
     */
    public function setDisplayTableRole(?string $displayTableRole): self
    {
        $this->displayTableRole = $displayTableRole;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Settings
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Settings
     */
    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return Settings
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Settings
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }
}