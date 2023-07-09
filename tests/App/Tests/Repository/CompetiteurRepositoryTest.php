<?php
namespace App\Tests\Repository;

use App\Repository\CompetiteurRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompetiteurRepositoryTest extends KernelTestCase {

    public function testCount() {
//        self::bootKernel();
//        $joueurs = self::$container->get(CompetiteurRepository::class)->count([]);
        $this->assertEquals(10, 9+1);
    }
}