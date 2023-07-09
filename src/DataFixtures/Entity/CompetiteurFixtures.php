<?php

namespace App\DataFixtures\Entity;

use App\Entity\Competiteur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CompetiteurFixtures extends Fixture {

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $joueur = (new Competiteur())
                ->setUsername('pseudo')
                ->setPassword('000');
            $manager->persist($joueur);
        }
        $manager->flush();
    }
}