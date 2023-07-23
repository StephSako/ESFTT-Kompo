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
    public function setAutreFichier(Reglement $autreFichier): Tournoi
    {
        $this->autreFichier = $autreFichier;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDotationTotale(): ?string
    {
        return $this->dotationTotale ? floatval($this->dotationTotale / 100) . '€' : '';
    }

    /**
     * @param string|null $dotationTotale
     * @return Tournoi
     */
    public function setDotationTotale(?string $dotationTotale): Tournoi
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
    public function setType(string $type): Tournoi
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
    public function setId(string $id): Tournoi
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
    public function setStartDate(?DateTime $startDate): Tournoi
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
    public function setEndDate(?DateTime $endDate): Tournoi
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
    public function setClubName(string $clubName): Tournoi
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
    public function setAddress(Adresse $address): Tournoi
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
    public function setName(string $name): Tournoi
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
    public function setReglement(Reglement $reglement): Tournoi
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
    public function setTableaux(array $tableaux): Tournoi
    {
        $this->tableaux = array_map(function ($tableau) {
            return new Tableau($tableau);
        }, $tableaux);
        return $this;
    }
}