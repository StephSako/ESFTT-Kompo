<?php

namespace App\Entity\TournoiFFTT;

use App\Controller\UtilController;

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
        return UtilController::MAPS_URI . $this->GPSAddressEncoded();
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
        return UtilController::formatAddress($this->getStreetAddress(), $this->getPostalCode(), $this->getAddressLocality());
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
        return UtilController::IFRAME_URI . $this->GPSAddressEncoded();
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
        return UtilController::WAZE_URI . $this->GPSAddressEncoded();
    }
}