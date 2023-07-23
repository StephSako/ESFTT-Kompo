<?php

namespace App\Entity\TournoiFFTT;

use DateTime;
use Exception;

class Tournoi
{
    /** @var string */
    private $id;
    /** @var string */
    private $type;
    /** @var DateTime|null */
    private $startDate;
    /** @var DateTime|null */
    private $endDate;
    /** @var string */
    private $clubName;
    /** @var string|null */
    private $dotationTotale;
    /** @var Adresse */
    private $address;
    /** @var string */
    private $name;
    /** @var Reglement */
    private $reglement;
    /** @var Reglement */
    private $autreFichier;
    /** @var Tableau[] */
    private $tableaux;
    /** @var string|null */
    private $page;

    function __construct($item)
    {
        $this->setDotationTotale($item['endowment']);
        $this->setAddress(new Adresse($item['address']));
        $this->setId($item['id']);
        $this->setName($item['name']);
        try {
            $this->setStartDate(new DateTime($item['startDate']));
        } catch (Exception $e) {
            $this->setStartDate(null);
        }
        try {
            $this->setEndDate(new DateTime($item['endDate']));
        } catch (Exception $e) {
            $this->setEndDate(null);
        }
        $this->setReglement(new Reglement($item['rules']));
        $this->setAutreFichier(new Reglement($item['engagmentSheet']));
        $this->setClubName($item['club']['name']);
        $this->setTableaux($item['tables']);
        $this->setType($item['type']);
        $this->setPage($item['page']);
    }

    /**
     * @return string|null
     */
    public function getPage(): ?string
    {
        return $this->page;
    }

    /**
     * @param string|null $page
     * @return Tournoi
     */
    public function setPage(?string $page): self
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return Reglement
     */
    public function getAutreFichier(): Reglement
    {
        return $this->autreFichier;
    }

    /**
     * @param Reglement $autreFichier
     * @return Tournoi
     */
    public function setAutreFichier(Reglement $autreFichier): self
    {
        $this->autreFichier = $autreFichier;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDotationTotale(): ?string
    {
        return $this->dotationTotale ? number_format(floatval($this->dotationTotale / 100), 0, ',', ' ') . '€' : '';
    }

    /**
     * @param string|null $dotationTotale
     * @return Tournoi
     */
    public function setDotationTotale(?string $dotationTotale): self
    {
        $this->dotationTotale = $dotationTotale;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Tournoi
     */
    public function setType(string $type): self
    {
        switch ($type) {
            case 'I':
                $this->type = 'International';
                break;
            case 'A':
                $this->type = 'National A';
                break;
            case 'B':
                $this->type = 'National B';
                break;
            case 'R':
                $this->type = 'Régional';
                break;
            case 'D':
                $this->type = 'Départemental';
                break;
            case 'P':
                $this->type = 'Promotionnel';
                break;
            default:
                $this->type = 'Indéfini';
                break;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Tournoi
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime|null $startDate
     * @return Tournoi
     */
    public function setStartDate(?DateTime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime|null $endDate
     * @return Tournoi
     */
    public function setEndDate(?DateTime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getClubName(): string
    {
        return $this->clubName;
    }

    /**
     * @param string $clubName
     * @return Tournoi
     */
    public function setClubName(string $clubName): self
    {
        $this->clubName = $clubName;
        return $this;
    }

    /**
     * @return Adresse
     */
    public function getAddress(): Adresse
    {
        return $this->address;
    }

    /**
     * @param Adresse $address
     * @return Tournoi
     */
    public function setAddress(Adresse $address): self
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Tournoi
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Reglement
     */
    public function getReglement(): Reglement
    {
        return $this->reglement;
    }

    /**
     * @param Reglement $reglement
     * @return Tournoi
     */
    public function setReglement(Reglement $reglement): self
    {
        $this->reglement = $reglement;
        return $this;
    }

    /**
     * @return Tableau[]
     */
    public function getTableaux(): array
    {
        return $this->tableaux;
    }

    /**
     * @param Tableau[] $tableaux
     * @return Tournoi
     */
    public function setTableaux(array $tableaux): self
    {
        $this->tableaux = array_map(function ($tableau) {
            return new Tableau($tableau);
        }, $tableaux);
        return $this;
    }
}