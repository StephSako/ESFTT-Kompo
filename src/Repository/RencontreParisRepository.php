<?php

namespace App\Repository;

use App\Entity\RencontreParis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RencontreParis|null find($id, $lockMode = null, $lockVersion = null)
 * @method RencontreParis|null findOneBy(array $criteria, array $orderBy = null)
 * @method RencontreParis[]    findAll()
 * @method RencontreParis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RencontreParisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RencontreParis::class);
    }

    /**
     * @param $compos
     * @return array
     */
    public function getSelectedPlayers($compos): array
    {
        $selectedPlayers = [];
        foreach ($compos as $compo){
            if ($compo->getIdJoueur1() != null) array_push($selectedPlayers, $compo->getIdJoueur1()->getIdCompetiteur());
            if ($compo->getIdJoueur2() != null) array_push($selectedPlayers, $compo->getIdJoueur2()->getIdCompetiteur());
            if ($compo->getIdJoueur3() != null) array_push($selectedPlayers, $compo->getIdJoueur3()->getIdCompetiteur());
            if ($compo->getIdJoueur4() != null) array_push($selectedPlayers, $compo->getIdJoueur4()->getIdCompetiteur());
            if ($compo->getIdJoueur5() != null) array_push($selectedPlayers, $compo->getIdJoueur5()->getIdCompetiteur());
            if ($compo->getIdJoueur6() != null) array_push($selectedPlayers, $compo->getIdJoueur6()->getIdCompetiteur());
            if ($compo->getIdJoueur7() != null) array_push($selectedPlayers, $compo->getIdJoueur7()->getIdCompetiteur());
            if ($compo->getIdJoueur8() != null) array_push($selectedPlayers, $compo->getIdJoueur8()->getIdCompetiteur());
            if ($compo->getIdJoueur9() != null) array_push($selectedPlayers, $compo->getIdJoueur9()->getIdCompetiteur());
        }
        return $selectedPlayers;
    }

    /**
     * @return int|mixed|string
     */
    public function getOrderedRencontres(){
        return $this->createQueryBuilder('c')
            ->leftJoin('c.idJournee', 'j')
            ->orderBy('j.date')
            ->addOrderBy('c.idJournee')
            ->addOrderBy('c.idEquipe')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $idCompetiteur
     * @param $idJournee
     * @return int|mixed|string
     */
    public function getSelectedWhenBurnt($idCompetiteur, $idJournee){
        return $this->createQueryBuilder('rp')
            ->select('rp as compo')
            ->addSelect("IF(rp.idJoueur1=:idCompetiteur, 1, 0) as isPlayer1")
            ->addSelect("IF(rp.idJoueur2=:idCompetiteur, 1, 0) as isPlayer2")
            ->addSelect("IF(rp.idJoueur3=:idCompetiteur, 1, 0) as isPlayer3")
            ->addSelect("IF(rp.idJoueur4=:idCompetiteur, 1, 0) as isPlayer4")
            ->addSelect("IF(rp.idJoueur5=:idCompetiteur, 1, 0) as isPlayer5")
            ->addSelect("IF(rp.idJoueur6=:idCompetiteur, 1, 0) as isPlayer6")
            ->addSelect("IF(rp.idJoueur7=:idCompetiteur, 1, 0) as isPlayer7")
            ->addSelect("IF(rp.idJoueur8=:idCompetiteur, 1, 0) as isPlayer8")
            ->addSelect("IF(rp.idJoueur9=:idCompetiteur, 1, 0) as isPlayer9")
            ->from('App:Competiteur', 'c')
            ->where('rp.idJournee > :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('rp.idJournee <= 7')
            ->andWhere('rp.idEquipe = 2')
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere('rp.idJoueur1 = c.idCompetiteur OR rp.idJoueur2 = c.idCompetiteur OR rp.idJoueur3 = c.idCompetiteur OR rp.idJoueur4 = c.idCompetiteur OR rp.idJoueur5 = c.idCompetiteur OR rp.idJoueur6 = c.idCompetiteur OR rp.idJoueur7 = c.idCompetiteur OR rp.idJoueur8 = c.idCompetiteur OR rp.idJoueur9 = c.idCompetiteur')
            ->andWhere("(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p WHERE (p.idJoueur1 = c.idCompetiteur OR p.idJoueur2 = c.idCompetiteur OR p.idJoueur3 = c.idCompetiteur OR p.idJoueur4 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur6 = c.idCompetiteur OR p.idJoueur7 = c.idCompetiteur OR p.idJoueur8 = c.idCompetiteur OR p.idJoueur9 = c.idCompetiteur) AND p.idEquipe = 1) >= 2")
            ->getQuery()->getResult();
    }

    /**
     * @param $idCompetiteur
     * @param $idJournee
     * @return int|mixed|string
     */
    public function getSelectedWhenIndispo($idCompetiteur, $idJournee){
        return $this->createQueryBuilder('rp')
            ->select('rp as compo')
            ->addSelect("IF(rp.idJoueur1=:idCompetiteur, 1, 0) as isPlayer1")
            ->addSelect("IF(rp.idJoueur2=:idCompetiteur, 1, 0) as isPlayer2")
            ->addSelect("IF(rp.idJoueur3=:idCompetiteur, 1, 0) as isPlayer3")
            ->addSelect("IF(rp.idJoueur4=:idCompetiteur, 1, 0) as isPlayer4")
            ->addSelect("IF(rp.idJoueur5=:idCompetiteur, 1, 0) as isPlayer5")
            ->addSelect("IF(rp.idJoueur6=:idCompetiteur, 1, 0) as isPlayer6")
            ->addSelect("IF(rp.idJoueur7=:idCompetiteur, 1, 0) as isPlayer7")
            ->addSelect("IF(rp.idJoueur8=:idCompetiteur, 1, 0) as isPlayer8")
            ->addSelect("IF(rp.idJoueur9=:idCompetiteur, 1, 0) as isPlayer9")
            ->from('App:Competiteur', 'c')
            ->where('rp.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere('rp.idJoueur1 = c.idCompetiteur OR rp.idJoueur2 = c.idCompetiteur OR rp.idJoueur3 = c.idCompetiteur OR rp.idJoueur4 = c.idCompetiteur OR rp.idJoueur5 = c.idCompetiteur OR rp.idJoueur6 = c.idCompetiteur OR rp.idJoueur7 = c.idCompetiteur OR rp.idJoueur8 = c.idCompetiteur OR rp.idJoueur9 = c.idCompetiteur')
            ->getQuery()->getResult();
    }

    /**
     * Récupère la liste des joueurs devant être au plus 1 sélectionnés dans l'équipe
     * @param $idEquipe
     * @return int|mixed|string
     */
    public function getBrulesJ2($idEquipe){
        $composJ1 = $this->createQueryBuilder('rd')
            ->select('(rd.idJoueur1) as joueur1')
            ->addSelect('(rd.idJoueur2) as joueur2')
            ->addSelect('(rd.idJoueur3) as joueur3')
            ->addSelect('(rd.idJoueur4) as joueur4')
            ->addSelect('(rd.idJoueur5) as joueur5')
            ->addSelect('(rd.idJoueur6) as joueur6')
            ->addSelect('(rd.idJoueur7) as joueur7')
            ->addSelect('(rd.idJoueur8) as joueur8')
            ->addSelect('(rd.idJoueur9) as joueur9')
            ->where('rd.idEquipe < :idEquipe')
            ->andWhere('rd.idJournee = 1')
            ->setParameter('idEquipe', $idEquipe)
            ->getQuery()->getResult();

        $brulesJ2 = [];
        foreach ($composJ1 as $compo){
            foreach ($compo as $idJoueur){
                array_push($brulesJ2, $idJoueur);
            }
        }

        return $brulesJ2;
    }
}
