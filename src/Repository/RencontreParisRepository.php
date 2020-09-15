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
    public function getSelectedPlayers($compos){
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
            ->andWhere("JSON_VALUE(c.brulageParis, '$.1') >= 3")
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
}
