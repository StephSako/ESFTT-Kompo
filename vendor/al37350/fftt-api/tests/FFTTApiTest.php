<?php

namespace FFTTApi\Tests;

use FFTTApi\Exception\ClubNotFoundException;
use FFTTApi\Exception\InvalidCredidentials;
use FFTTApi\Exception\JoueurNotFound;
use FFTTApi\Exception\NoFFTTResponseException;
use FFTTApi\FFTTApi;
use FFTTApi\Model\Actualite;
use FFTTApi\Model\Classement;
use FFTTApi\Model\Club;
use FFTTApi\Model\ClubDetails;
use FFTTApi\Model\Equipe;
use FFTTApi\Model\Historique;
use FFTTApi\Model\Joueur;
use FFTTApi\Model\JoueurDetails;
use FFTTApi\Model\Organisme;
use FFTTApi\Model\Partie;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Created by Antoine Lamirault.
 */
class FFTTApiTest extends TestCase
{
    public function testInitializeWithGoodParameters()
    {
        $api = $this->generateGoodAPI();
        $this->assertInternalType('array', $api->initialize());
    }

    public function testInitializeWithBadParameters()
    {
        $this->expectException(InvalidCredidentials::class);

        $api = new FFTTApi("Bad", "credidentials");
        $api->initialize();
    }

    public function testGetOrganismeZone()
    {
        $api = $this->generateGoodAPI();
        $organismes = $api->getOrganismes("Z");
        $this->assertInternalType('array', $organismes);
        $this->assertInstanceOf(Organisme::class, $organismes[0]);
    }

    public function testGetOrganismeLigue()
    {
        $api = $this->generateGoodAPI();
        $organismes = $api->getOrganismes("L");
        $this->assertInternalType('array', $organismes);
        $this->assertInstanceOf(Organisme::class, $organismes[0]);
    }

    public function testGetOrganismeDepartement()
    {
        $api = $this->generateGoodAPI();
        $organismes = $api->getOrganismes("D");
        $this->assertInternalType('array', $organismes);
        $this->assertInstanceOf(Organisme::class, $organismes[0]);
    }


    public function testGetClubsByDepartementWithRealDepartement()
    {
        $api = $this->generateGoodAPI();
        $this->assertInternalType('array', $api->getClubsByDepartement(37));
    }

    public function testGetClubsByDepartementWithBadDepartement()
    {
        $this->expectException(NoFFTTResponseException::class);

        $api = $this->generateGoodAPI();
        $api->getClubsByDepartement(500);
    }

    public function testGetClubDetailsWithGoodNumero()
    {
        $api = $this->generateGoodAPI();
        $club = $api->getClubDetails("04370690");
        $this->assertInstanceOf(ClubDetails::class, $club);
    }

    public function testGetClubDetailsWithBadNumero()
    {
        $this->expectException(ClubNotFoundException::class);

        $api = $this->generateGoodAPI();
        $api->getClubDetails("123");
    }

    public function testGetJoueursByClubWithGoodNumero()
    {
        $api = $this->generateGoodAPI();
        $joueurs = $api->getJoueursByClub("04370690");
        if (count($joueurs)) {
            $this->assertInstanceOf(Joueur::class, $joueurs[0]);
        }
    }

    public function testGetJoueursByClubWithBadNumero()
    {
        $this->expectException(ClubNotFoundException::class);

        $api = $this->generateGoodAPI();
        $api->getJoueursByClub("500");
    }

    public function testGetJoueursByNomWithGoodNom()
    {
        $api = $this->generateGoodAPI();
        $joueurs = $api->getJoueursByNom("Lamirault");
        if (count($joueurs)) {
            $this->assertInstanceOf(Joueur::class, $joueurs[0]);
        }
    }

    public function testGetJoueursByNomWithNomWithSingleQuote()
    {
        $api = $this->generateGoodAPI();
        $joueurs = $api->getJoueursByNom("D'ANG");
            $this->assertInstanceOf(Joueur::class, $joueurs[0]);
    }

    public function testGetJoueursByNomWithGoodNomBadPrenom()
    {
        $this->expectException(NoFFTTResponseException::class);

        $api = $this->generateGoodAPI();
        $api->getJoueursByNom("Lamirault", "helloooooooooooo");
    }

    public function testGetJoueursByNomWithBadNom()
    {
        $this->expectException(NoFFTTResponseException::class);

        $api = $this->generateGoodAPI();
        $api->getJoueursByNom("azertyuiop");
    }

    public function testGetJoueurDetailsByLicenceWithGoodLicence()
    {
        $api = $this->generateGoodAPI();
        $joueur = $api->getJoueurDetailsByLicence(3719655);
        $this->assertInstanceOf(JoueurDetails::class, $joueur);
    }

    public function testGetJoueurDetailsByLicenceWithBadLicence()
    {
        $this->expectException(JoueurNotFound::class);

        $api = $this->generateGoodAPI();
        $api->getJoueurDetailsByLicence(-12);
    }

    public function testGetClassementJoueurByLicenceWithGoodLicence()
    {
        $api = $this->generateGoodAPI();
        $classement = $api->getClassementJoueurByLicence(3719655);
        $this->assertInstanceOf(Classement::class, $classement);
    }

    public function testGetClassementJoueurByLicenceWithBadLicence()
    {
        $this->expectException(JoueurNotFound::class);

        $api = $this->generateGoodAPI();
        $api->getClassementJoueurByLicence(-12);
    }

    public function testGetPartiesJoueurByLicenceWithGoodLicence()
    {
        $api = $this->generateGoodAPI();
        $parties = $api->getPartiesJoueurByLicence(3719655);
        $this->assertInternalType('array', $parties);
        if (count($parties)) {
            $this->assertInstanceOf(Partie::class, $parties[0]);
        }
    }

    public function testGetPartiesJoueurByLicenceWithBadLicence()
    {
        $api = $this->generateGoodAPI();
        $parties = $api->getPartiesJoueurByLicence(-12);
        $this->assertInternalType('array', $parties);
        $this->assertCount(0, $parties);
    }

    public function testGetEquipesByClubWithGoodNumero()
    {
        $api = $this->generateGoodAPI();
        $equipes = $api->getEquipesByClub("04370690", 'M');
        $this->assertInternalType('array', $equipes);
        if (count($equipes)) {
            $this->assertInstanceOf(Equipe::class, $equipes[0]);
        }
    }

    public function testGetEquipesByClubWithBadNumero()
    {
        $this->expectException(NoFFTTResponseException::class);

        $api = $this->generateGoodAPI();
        $api->getEquipesByClub("-12");
    }

    public function testGetHistoriqueJoueurByLicenceWithGoodLicence()
    {
        $api = $this->generateGoodAPI();
        $historiques = $api->getHistoriqueJoueurByLicence(3719655);
        if (count($historiques)) {
            $this->assertInstanceOf(Historique::class, $historiques[0]);
        }
    }

    public function testGetHistoriqueJoueurByLicenceWithBadLicence()
    {
        $this->expectException(JoueurNotFound::class);

        $api = $this->generateGoodAPI();
        $api->getHistoriqueJoueurByLicence(-12);
    }

    public function testGetClubWithName()
    {
        $api = $this->generateGoodAPI();
        $clubs = $api->getClubsByName("seno");
        $this->assertInternalType('array', $clubs);
        if (count($clubs)) {
            $this->assertInstanceOf(Club::class, $clubs[0]);
        }
    }

    public function testGetActualites()
    {
        $api = $this->generateGoodAPI();
        $actualites = $api->getActualites();
        $this->assertInternalType('array', $actualites);
        if (count($actualites)) {
            $this->assertInstanceOf(Actualite::class, $actualites[0]);
        }
    }

    private function generateGoodAPI(): FFTTApi
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/.env');


        return new FFTTApi(getenv("FFTT_PASSWORD"), getenv("FFTT_ID"));
    }
}