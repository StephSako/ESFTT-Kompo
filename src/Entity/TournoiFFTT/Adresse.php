<?php

namespace App\Entity\TournoiFFTT;

class Adresse
{
    function __construct($item) {
        $this->setDisambiguatingDescription($item['disambiguatingDescription']);
        $this->setPostalCode($item['postalCode']);
        $this->setStreetAddress($item['streetAddress']);
        $this->setAddressLocality(mb_convert_case($item['addressLocality'], MB_CASE_TITLE, "UTF-8"));
    }

    /** @var string */
    private $postalCode;

    /** @var string */
    private $streetAddress;

    /** @var string|null */
    private $disambiguatingDescription;

    /** @var string */
    private $addressLocality;

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
    public function setPostalCode(string $postalCode): Adresse
    {
        $this->postalCode = $postalCode;
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
}