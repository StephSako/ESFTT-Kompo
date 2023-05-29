<?php

namespace App\Entity\TournoiFFTT;

use DateTime;
use Exception;

class Tournoi
{
    function __construct($item) {
        $this->setAddress(new Adresse($item['address']));
        $this->setId($item['@id']);
        $this->setName($item['name']);
        try {
            $this->setStartDate(new DateTime($item['startDate']));
        } catch (Exception $e) {
            $this->setStartDate(null);
        }
        try {
            $this->setEndDate(new DateTime($item['startDate']));
        } catch (Exception $e) {
            $this->setEndDate(null);
        }
        $this->setReglement(new Reglement($item['rules']));
        $this->setClubName($item['club']['name']);
        $this->setTableaux($item['tables']);
    }

    /** @var string */
    private $id;

    /** @var DateTime|null */
    private $startDate;

    /** @var DateTime|null */
    private $endDate;

    /** @var string */
    private $clubName;

    /** @var Adresse */
    private $address;

    /** @var string */
    private $name;

    /** @var Reglement */
    private $reglement;

    /** @var Tableau[] */
    private $tableaux;

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