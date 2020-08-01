<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JourneeDepartementaleRepository")
 * @ORM\Table(name="journee_departementale")
 */
class JourneeDepartementale
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_journee")
     */
    private $idJournee;

    /**
     * @var DateTime
     * @ORM\Column(type="date", name="date", nullable=false)
     */
    private $date;

    /**
     * @var String
     */
    private $type = 'Départementale';

    /**
     * @return String
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @var String
     */
    private $linkType = 'departementale';

    /**
     * @return String
     */
    public function getLinkType(): string
    {
        return $this->linkType;
    }

    /**
     * @return mixed
     */
    public function getIdJournee()
    {
        return $this->idJournee;
    }

    /**
     * @param mixed $idJournee
     * @return JourneeDepartementale
     */
    public function setIdJournee($idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return JourneeDepartementale
     */
    public function setDate(Datetime $date): self
    {
        $this->date = $date;
        return $this;
    }
}