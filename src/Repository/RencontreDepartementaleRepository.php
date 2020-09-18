<?php

namespace App\Repository;

use App\Entity\RencontreDepartementale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RencontreDepartementale|null find($id, $lockMode = null, $lockVersion = null)
 * @method RencontreDepartementale|null findOneBy(array $criteria, array $orderBy = null)
 * @method RencontreDepartementale[]    findAll()
 * @method RencontreDepartementale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RencontreDepartementaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RencontreDepartementale::class);
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
     * @param $idEquipe
     * @return int|mixed|string
     */
    public function getSelectedWhenBurnt($idCompetiteur, $idJournee, $idEquipe){
        return $this->createQueryBuilder('rd')
            ->select('rd as compo')
            ->addSelect("IF(rd.idJoueur1=:idCompetiteur, 1, 0) as isPlayer1")
            ->addSelect("IF(rd.idJoueur2=:idCompetiteur, 1, 0) as isPlayer2")
            ->addSelect("IF(rd.idJoueur3=:idCompetiteur, 1, 0) as isPlayer3")
            ->addSelect("IF(rd.idJoueur4=:idCompetiteur, 1, 0) as isPlayer4")
            ->from('App:Competiteur', 'c')
            ->where('rd.idJournee > :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('rd.idEquipe > :idEquipe')
            ->setParameter('idEquipe', $idEquipe)
            ->andWhere('rd.idJournee <= 7')
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere('rd.idJoueur1 = c.idCompetiteur OR rd.idJoueur2 = c.idCompetiteur OR rd.idJoueur3 = c.idCompetiteur OR rd.idJoueur4 = c.idCompetiteur')
            ->andWhere("(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idEquipe = 1) >= 1 OR (SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idEquipe = 2) >= 1 OR (SELECT COUNT(p3.id) FROM App\Entity\RencontreDepartementale p3 WHERE (p3.idJoueur1 = c.idCompetiteur OR p3.idJoueur2 = c.idCompetiteur OR p3.idJoueur3 = c.idCompetiteur OR p3.idJoueur4 = c.idCompetiteur) AND p3.idEquipe = 3) >= 1")
            ->getQuery()->getResult();
    }

    /**
     * @param $idCompetiteur
     * @param $idJournee
     * @return int|mixed|string
     */
    public function getSelectedWhenIndispo($idCompetiteur, $idJournee){
        return $this->createQueryBuilder('rd')
            ->select('rd as compo')
            ->addSelect("IF(rd.idJoueur1=:idCompetiteur, 1, 0) as isPlayer1")
            ->addSelect("IF(rd.idJoueur2=:idCompetiteur, 1, 0) as isPlayer2")
            ->addSelect("IF(rd.idJoueur3=:idCompetiteur, 1, 0) as isPlayer3")
            ->addSelect("IF(rd.idJoueur4=:idCompetiteur, 1, 0) as isPlayer4")
            ->from('App:Competiteur', 'c')
            ->where('rd.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere('rd.idJoueur1 = c.idCompetiteur OR rd.idJoueur2 = c.idCompetiteur OR rd.idJoueur3 = c.idCompetiteur OR rd.idJoueur4 = c.idCompetiteur')
            ->getQuery()->getResult();
    }
}
