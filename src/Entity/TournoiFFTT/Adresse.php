<?php

namespace App\Entity\TournoiFFTT;

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
     * @return string
     */
    public function getHrefMapsAdresse(): string
    {
        return 'https://www.google.com/maps/dir/?api=1&travelmode=driving&destination=' . $this->GPSAddressEncoded();
    }

    /**
     * Retourne l'addresse encodée pour URL
     * @return string
     */
    public function GPSAddressEncoded(): string
    {
        return urlencode($this->GPSAddress());
    }

    /**
     * Retourne l'addresse
     * @return string
     */
    public function GPSAddress(): string
    {
        return $this->getStreetAddress() .
            ($this->getPostalCode() ? ', ' . $this->getPostalCode() : '') .
            ($this->getAddressLocality() ? ', ' . $this->getAddressLocality() : '');
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
     * @return string
     */
    public function getHrefIframeLink(): string
    {
        return 'https://maps.google.com/maps?width=400&amp;height=300&amp;hl=fr&amp;q=' . $this->GPSAddressEncoded() . '+()&amp;t=&amp;z=5&amp;ie=UTF8&amp;iwloc=B&amp;output=embed';
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
        return $this->GPSAddressEncoded();
    }
}