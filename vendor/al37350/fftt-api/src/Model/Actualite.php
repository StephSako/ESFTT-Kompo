<?php

namespace FFTTApi\Model;


class Actualite
{

    public $date;
    public $titre;
    public $description;
    public $url;
    public $photo;
    public $categorie;

    public function __construct(\DateTime $date, string $titre, string $description, string $url, string $photo, string $categorie)
    {
        $this->date = $date;
        $this->titre = $titre;
        $this->description = $description;
        $this->url = $url;
        $this->photo = $photo;
        $this->categorie = $categorie;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getPhoto(): string
    {
        return $this->photo;
    }

    public function getCategorie(): string
    {
        return $this->categorie;
    }

}