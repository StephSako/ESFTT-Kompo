<?php
/**
 * Created by PhpStorm.
 * User: alamirault
 * Date: 27/11/18
 * Time: 19:24
 */

namespace FFTTApi\Model;


class Organisme
{
    /**
     * @var string
     */
    private $libelle;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var int
     */
    private $idpere;

    /**
     * Organisme constructor.
     * @param string $libelle
     * @param int $id
     * @param string $code
     * @param int $idpere
     */
    public function __construct(string $libelle, int $id, string $code, int $idpere)
    {
        $this->libelle = $libelle;
        $this->id = $id;
        $this->code = $code;
        $this->idpere = $idpere;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getIdpere(): int
    {
        return $this->idpere;
    }
}