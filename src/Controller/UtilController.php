<?php

namespace App\Controller;

use App\Entity\Championnat;
use App\Entity\Journee;
use App\Entity\Rencontre;
use App\Repository\ChampionnatRepository;
use DateInterval;
use DateTime;
use Exception;
use App\Repository\RencontreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilController extends AbstractController
{
    private $rencontreRepository;
    private $championnatRepository;

    /**
     * @param RencontreRepository $rencontreRepository
     * @param ChampionnatRepository $championnatRepository
     */
    public function __construct(RencontreRepository $rencontreRepository,
                                ChampionnatRepository $championnatRepository)
    {
        $this->rencontreRepository = $rencontreRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * Retourne un message selon que le championnat est terminé ou pas pour autoriser la Pré-phase
     * @param Championnat $championnat
     * @return array
     */
    function isPreRentreeLaunchable(Championnat $championnat): array {
        $latestDate = $this->getLastDates($championnat);
        if (!count($latestDate)) return ['launchable' => false, 'message' => 'Ce championnat n\'a pas d\'équipes enregistrées'];
        else if (max($latestDate) < new DateTime()) return ['launchable' => true, 'message' => 'La phase est terminée et la Pré-phase prête à être lancée'];
        else return ['launchable' => false, 'message' => 'La phase n\'est pas terminée pour lancer la pré-phase'];
    }

    /**
     * Génère un token afin de modifier le mot de passe d'un utilisateur en passant l'username et le date changer (combien de temps
     * le token est valide) en paramètre
     * @param string $username
     * @param string $dateChanger
     * @return string
     * @throws Exception
     */
    public function generateGeneratePasswordLink(string $username, string $dateChanger): string {
        $token = json_encode(
            [
                'username' => $username,
                'dateValidation' => (new DateTime())->add(new DateInterval($dateChanger))->getTimestamp()
            ]);
        $encryption_iv = hex2bin($this->getParameter('encryption_iv'));
        $encryption_key = openssl_digest(php_uname(), 'MD5', TRUE);
        return 'https://www.prive.esftt.com/login/reset_password/' . base64_encode(openssl_encrypt($token, "BF-CBC", $encryption_key, 0, $encryption_iv));
    }

    /**
     * @param int $limiteBrulage
     * @param int $idChampionnat
     * @param int $idJoueur
     * @param int $nbJoueurs
     * @param int $idJournee
     */
    public function checkInvalidSelection(int $limiteBrulage, int $idChampionnat, int $idJoueur, int $nbJoueurs, int $idJournee){
        $this->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenBurnt($idJoueur, $idJournee, $limiteBrulage, $nbJoueurs, $idChampionnat), $nbJoueurs, $idJoueur);
    }

    /**
     * @param array $invalidCompos
     * @param int $nbJoueurs
     * @param int $idCompetiteur
     */
    public function deleteInvalidSelectedPlayers(array $invalidCompos, int $nbJoueurs, int $idCompetiteur){
        foreach ($invalidCompos as $compo){
            $i = 0;
            while($i != $nbJoueurs){
                if ($compo['isPlayer' . $i] == $idCompetiteur){
                    $compo['compo']->setIdJoueurN($i, null);
                    break;
                }
                $i++;
            }
        }
    }

    /**
     * Retourne la prochaine journée à jouer depuis tous les championnats
     * @return Journee
     */
    public function nextJourneeToPlayAllChamps(): Journee {
        $allChamps = $this->championnatRepository->findAll();
        $array = array_filter(array_map(function($c) {
            return $c->getNextJourneeToPlay();
        }, $allChamps), function($cNJTP) {
            return $cNJTP;
        });

         /** Si toutes les dates de tous les championnats sont indéfinies */
        if (!$array) return $allChamps[0]->getJournees()[0];

        usort($array, function($a, $b) use ($allChamps) {
            if (!$a && !$b) return $allChamps[0]->getJournees()[0]->getDateJournee()->getTimestamp();
            if (!$a) return $b->getDateJournee()->getTimestamp();
            if (!$b) return $a->getDateJournee()->getTimestamp();
            return $a->getDateJournee()->getTimestamp() - $b->getDateJournee()->getTimestamp();
        });
        return array_shift($array);
    }

    /**
     * Retourne les dates au plus tard de toutes les recontres du championnat sélectionné
     * @param Championnat $championnat
     * @return array
     */
    function getLastDates(Championnat $championnat): array
    {
        return array_unique(array_map(function(Rencontre $renc) {
            return max([$renc->isReporte() ? $renc->getDateReport() : null, $renc->getIdJournee()->getDateJournee()]);
        }, $championnat->getRencontres()->toArray()), SORT_REGULAR);
    }
}
