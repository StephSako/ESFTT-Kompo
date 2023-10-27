<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi\Tests;

use FFTTApi\Model\Equipe;
use FFTTApi\Service\PointCalculator;
use FFTTApi\Service\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testAccentNomPrenom(){
        list($nom , $prenom ) = Utils::returnNomPrenom("MOREAU Véronique");

        $this->assertEquals("MOREAU", $nom);
        $this->assertEquals("Véronique", $prenom);
    }

    public function testNomComposeNomPrenom(){
        list($nom , $prenom ) = Utils::returnNomPrenom("DA COSTA TEIXEIRA Ana");

        $this->assertEquals("DA COSTA TEIXEIRA", $nom);
        $this->assertEquals("Ana", $prenom);
    }

    public function testExtractNomEquipe(){
        $equipeA =$this->createMock(Equipe::class);
        $equipeA->method("getLibelle")->willReturn("Test - ABC");

        $this->assertEquals("Test", Utils::extractNomEquipe($equipeA));

        $equipeB =$this->createMock(Equipe::class);
        $equipeB->method("getLibelle")->willReturn("Test ABC");

        $this->assertEquals("Test ABC", Utils::extractNomEquipe($equipeB));

        $equipeC =$this->createMock(Equipe::class);
        $equipeC->method("getLibelle")->willReturn("Test - ABC - EFG");

        $this->assertEquals("Test - ABC - EFG", Utils::extractNomEquipe($equipeC));
    }

    public function testExtractClubEquipe(){
        $equipeA =$this->createMock(Equipe::class);
        $equipeA->method("getLibelle")->willReturn("TOURS 4S TT 3 - Phase 1");

        $this->assertEquals("TOURS 4S TT", Utils::extractClub($equipeA));

        $equipeB =$this->createMock(Equipe::class);
        $equipeB->method("getLibelle")->willReturn("TOURS 4S TT - Phase 1");

        $this->assertEquals("TOURS 4S TT", Utils::extractClub($equipeA));
    }

    public function testRemoveAccentLowerCaseRegex(){
        $prenom = Utils::removeAccentLowerCaseRegex("Abdel-Jalil");
        $this->assertEquals("abdel-jalil", $prenom);

        $prenom = Utils::removeAccentLowerCaseRegex("J?r?my");
        $this->assertEquals("j.r.my", $prenom);

        $prenom = Utils::removeAccentLowerCaseRegex("Jérémy");
        $this->assertEquals("jeremy", $prenom);
    }
}