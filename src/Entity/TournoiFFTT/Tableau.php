<?php

namespace App\Entity\TournoiFFTT;

use DateTime;
use Exception;

class Tableau
{
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
    /** @var number|null */
    private $cout;
    /** @var number */
    private $dotation;
    /** @var string */
    private $genres;
    /** @var string */
    private $typesLicences;
    /** @var number */
    private $id;

    function __construct($item)
    {
        $this->setId($item['id']);
        $this->setCout($item['fee']);
        try {
            $this->setDate($item['date'] ? new DateTime($item['date']) : null);
        } catch (Exception $e) {
        }
        $this->setTime($item['time']);
        $this->setDescription($item['description']);
        $this->setDotation($item['endowment']);
        $this->setName($item['name']);
        $this->setType($item['type']);
        $this->setGenres($item);
        $this->setTypesLicences($item);
    }

    /**
     * @return number
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param number $id
     * @return Tableau
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypesLicences(): string
    {
        return $this->typesLicences;
    }

    /**
     * @param array $item
     * @return Tableau
     */
    public function setTypesLicences(array $item): self
    {
        if (key_exists('licenceTypes', $item)) {
            $this->typesLicences = implode('/', array_map(function ($g) {
                return $g['name'];
            }, $item['licenceTypes']));
        } else $this->typesLicences = '';

        return $this;
    }

    /**
     * @return string
     */
    public function getGenres(): string
    {
        return $this->genres;
    }

    /**
     * @param array $item
     * @return Tableau
     */
    public function setGenres(array $item): self
    {
        if (key_exists('genders', $item)) {
            $this->genres = implode('/', array_map(function ($g) {
                return $g['name'][0];
            }, $item['genders']));
        } else $this->genres = 'Indéfini';
        
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
                return "Simple";
            case 'E':
                return "Équipe";
            case 'D':
                return "Double";
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
        if ($this->time != null) {
            $formattedTime = str_replace([':', '.', ' '], ['h', 'h', ''], mb_convert_case($this->time, MB_CASE_LOWER, "UTF-8"));

            preg_match_all('/^(?<heure>\d{2})$/', $formattedTime, $heure);
            if (count($heure['heure']) && $heure['heure'][0] == $formattedTime) $formattedTime = $heure['heure'][0] . 'h00';

            if ($formattedTime[0] == '0') $formattedTime = substr($formattedTime, 1);
            if ($formattedTime[strlen($formattedTime) - 1] == 'h') $formattedTime .= '00';

            return $formattedTime;
        } else return $this->time;
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
     * @return string
     */
    public function getCout(): string
    {
        return $this->cout ? number_format(floatval($this->cout / 100), 0, ',', ' ') . '€' : 'Gratuit';
    }

    /**
     * @param int|null $cout
     * @return Tableau
     */
    public function setCout(?int $cout): self
    {
        $this->cout = $cout;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDotation(): ?string
    {
        return $this->dotation ? number_format(floatval($this->dotation / 100), 0, ',', ' ') . '€' : null;
    }

    /**
     * @param int|null $dotation
     * @return Tableau
     */
    public function setDotation(?int $dotation): self
    {
        $this->dotation = $dotation;
        return $this;
    }
}