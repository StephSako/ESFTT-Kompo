<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Contact
{

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $titre;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $message;

    /**
     * @var Competiteur[]
     */
    private $competiteurs;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Contact
     */
    public function setMessage(string $message): Contact
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return Competiteur[]
     */
    public function getCompetiteurs(): array
    {
        return $this->competiteurs;
    }

    /**
     * @param Competiteur[] $competiteurs
     * @return Contact
     */
    public function setCompetiteurs(array $competiteurs): Contact
    {
        $this->competiteurs = $competiteurs;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitre(): string
    {
        return $this->titre;
    }

    /**
     * @param string $titre
     * @return Contact
     */
    public function setTitre(string $titre): Contact
    {
        $this->titre = $titre;
        return $this;
    }

}