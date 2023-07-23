<?php

namespace App\Entity\TournoiFFTT;

class Adresse
{
    /** @var string */
    private $postalCode;
    /** @var string */
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
    public function getDisambiguatingDescription(): ?string
    {
        return $this->disambiguatingDescription;
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
    public function getHrefMapsAdresse(): ?string
    {
        return str_replace(' ', '+', $this->GPSAddress());
    }

    /**
     * Retiurne l'addresse formattée pour GPS
     * @return string
     */
    public function GPSAddress(): string
    {
        return $this->getPostalCode() . ' ' . $this->getAddressLocality() . ' ' . $this->getStreetAddress();
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
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
        return $this->addressLocality;
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
     * @return string
     */
    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    /**
     * @param string $streetAddress
     * @return Adresse
     */
    public function setStreetAddress(string $streetAddress): self
    {
        $this->streetAddress = $streetAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHrefWazeAdresse(): ?string
    {
        return str_replace(' ', '%20', $this->GPSAddress());
    }
}