<?php

namespace App\Entity\TournoiFFTT;

use DateTime;
use Exception;

class Tableau
{
    function __construct($item) {
        $this->setFee($item['fee']);
        try {
            $this->setDate(new DateTime($item['date']));
        } catch (Exception $e) {
            $this->setDate(null);
        }
        $this->setTime($item['time']);
        $this->setDescription($item['description']);
        $this->setDotation($item['endowment']);
        $this->setName($item['name']);
        $this->setType($item['type']);
    }

    /** @var string */
    private $name;

    /**
     * I : individuel
     * E : double
     * @var string
     */
    private $type;

    /** @var string|null */
    private $description;

    /** @var DateTime|null */
    private $date;

    /** @var string|null */
    private $time;

    /** @var number */
    private $fee;

    /** @var number */
    private $dotation;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Tableau
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        switch ($this->type) {
            case 'I':
                return "Individuel";
            case 'E':
                return "Par équipe";
            default:
                return $this->type;
        }
    }

    /**
     * @param string $type
     * @return Tableau
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Tableau
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime|null $date
     * @return Tableau
     */
    public function setDate(?DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTime(): ?string
    {
        return $this->time;
    }

    /**
     * @param string|null $time
     * @return Tableau
     */
    public function setTime(?string $time): self
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return float
     */
    public function getFee(): float
    {
        return floatval($this->fee/100);
    }

    /**
     * @param number $fee
     * @return Tableau
     */
    public function setFee($fee): self
    {
        $this->fee = $fee;
        return $this;
    }

    /**
     * @return float
     */
    public function getDotation(): float
    {
        return floatval($this->dotation/100);
    }

    /**
     * @param number $dotation
     * @return Tableau
     */
    public function setDotation($dotation): self
    {
        $this->dotation = $dotation;
        return $this;
    }
}