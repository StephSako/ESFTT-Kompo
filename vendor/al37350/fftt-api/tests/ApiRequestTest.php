<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi\Tests;


use FFTTApi\ApiRequest;
use FFTTApi\Exception\InvalidURIParametersException;
use FFTTApi\Exception\URIPartNotValidException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class ApiRequestTest extends TestCase
{
    public function testGetWithBadUriPart(){
        $this->expectException(URIPartNotValidException::class);

        $request = $this->getApiRequest();
        $request->get('hello');
    }

    public function testGetWithGoodUriPartAndBadParameters(){
        $this->expectException(InvalidURIParametersException::class);

        $request = $this->getApiRequest();
        $request->get('xml_joueur', [
            'badKey' => 'value'
        ]);
    }
    private function getApiRequest(){
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/.env');


        return new ApiRequest(getenv("FFTT_PASSWORD"), getenv("FFTT_ID"));
    }
}