<?php

namespace App\Entity\TournoiFFTT;

use Transliterator;

class Adresse
{
    /** @var string */
    private $postalCode;
    /** @var string|null */
    private $streetAddress;
    /** @var string|null */
    private $disambiguatingDescription;
    /** @var string */
    private $addressLocality;

    function __construct($item)
    {
        $this->setDisambiguatingDescription($item['disambiguatingDescription']);
        $this->setPostalCode($item['postalCode']);
        $this->setStreetAddress($item['streetAddress']);
        $this->setAddressLocality(mb_convert_case($item['addressLocality'], MB_CASE_TITLE, "UTF-8"));
    }

    /**
     * @return string|null
     */
    public function getHrefMapsAdresse(): ?string
    {
        return str_replace(',', '%2C', str_replace(' ', '%20', 'https://www.google.com/maps/place/' .
            ($this->getStreetAddress() ?
                Transliterator::create('NFD; [:Nonspacing Mark:] Remove;')->transliterate($this->getStreetAddress()) : '') .
            ($this->getPostalCode() ? ',+' . Transliterator::create('NFD; [:Nonspacing Mark:] Remove;')->transliterate($this->getPostalCode()) : '') .
            ($this->getAddressLocality() ? ',+' . Transliterator::create('NFD; [:Nonspacing Mark:] Remove;')->transliterate($this->getAddressLocality()) : '') . '/'
        ));
    }

    /**
     * @return string|null
     */
    public function getStreetAddress(): ?string
    {
        return trim($this->streetAddress);
    }

    /**
     * @param string|null $streetAddress
     * @return Adresse
     */
    public function setStreetAddress(?string $streetAddress): self
    {
        $this->streetAddress = $streetAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return trim($this->postalCode);
    }

    /**
     * @param string $postalCode
     * @return Adresse
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLocality(): string
    {
        return trim($this->addressLocality);
    }

    /**
     * @param string $addressLocality
     * @return Adresse
     */
    public function setAddressLocality(string $addressLocality): self
    {
        $this->addressLocality = $addressLocality;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisambiguatingDescription(): ?string
    {
        return trim($this->disambiguatingDescription);
    }

    /**
     * @param string|null $disambiguatingDescription
     * @return Adresse
     */
    public function setDisambiguatingDescription(?string $disambiguatingDescription): self
    {
        $this->disambiguatingDescription = $disambiguatingDescription;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHrefWazeAdresse(): ?string
    {
        return str_replace(' ', '%20', $this->GPSAddress());
    }

    /**
     * Retiurne l'addresse formattée pour GPS
     * @return string
     */
    public function GPSAddress(): string
    {
        return $this->getPostalCode() . ' ' . $this->getAddressLocality() . ' ' . $this->getStreetAddress();
    }
}