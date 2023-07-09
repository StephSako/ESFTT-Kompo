<?php
namespace App\Tests\Entity;

use App\Entity\Competiteur;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompetiteurTest extends KernelTestCase {

    public function testValidEntity() {
        $joueur = (new Competiteur())
            ->setNom('nom')
            ->setPrenom('prenom')
            ->setUsername('pseudo')
            ->setPassword('000');
        self::bootKernel();
        $error = self::$container->get('validator')->validate($joueur);
        $this->assertCount(0, $error);
    }
}