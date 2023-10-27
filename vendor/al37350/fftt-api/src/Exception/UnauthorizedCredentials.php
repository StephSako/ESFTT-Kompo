<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi\Exception;


class UnauthorizedCredentials extends \Exception
{
    public function __construct(string $uri, string $content)
    {
        $xml = simplexml_load_string($content);
        $message = (string) $xml->erreur;
        parent::__construct(
            sprintf(
                "Non autorisé pour l'URL : '%s', message retourné : '%s'",
                $uri,
                $message
            )
        );
    }
}