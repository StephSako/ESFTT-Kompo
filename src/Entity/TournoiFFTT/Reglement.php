<?php

namespace App\Entity\TournoiFFTT;

use DateTime;

class Reglement
{
    function __construct($item) {
        $this->setUrl($item['url']);
        $this->setMimeType($item['mimeType']);
        $this->setOriginalFilename($item['originalFilename']);
    }

    /** @var string */
    private $originalFilename;

    /** @var string */
    private $mimeType;

    /** @var string */
    private $url;

    /**
     * @return string
     */
    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    /**
     * @param string $originalFilename
     * @return Reglement
     */
    public function setOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;
        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return Reglement
     */
    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Reglement
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }
}