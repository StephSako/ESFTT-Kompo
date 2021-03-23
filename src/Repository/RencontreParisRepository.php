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
     * @param RencontreParis[] $compos
     * @return array
     */
    public function getSelectedPlayers(array $compos): array
    {
        $selectedPlayers = [];
        foreach ($compos as $compo){
            if ($compo->getIdEquipe()->getIdDivision()) array_merge($selectedPlayers, $compo->getListSelectedPlayers($compo->getIdEquipe()->getIdDivision()->getNbJoueursChampParis()));
        }
        return $selectedPlayers;
    }

    /**
     * Récupère la liste des rencontres
     * @param int $idJournee
     * @return int|mixed|string
     */
    public function getRencontresParis(int $idJournee){
        return $this->createQueryBuilder('rp')
            ->leftJoin('rp.idEquipe', 'e')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('rp.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->orderBy('e.numero')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int|mixed|string
     */
    public function getOrderedRencontres(){
        return $this->createQueryBuilder('rp')
            ->leftJoin('rp.idJournee', 'j')
            ->orderBy('j.date')
            ->addOrderBy('rp.idJournee')
            ->addOrderBy('rp.idEquipe')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $idCompetiteur
     * @param int $idJournee
     * @param int $idEquipe
     * @param int $limitePreBrulage
     * @return int|mixed|string
     */
    public function getSelectedWhenBurnt(int $idCompetiteur, int $idJournee, int $idEquipe, int $limitePreBrulage){
        return $this->createQueryBuilder('rp')
            ->select('rp as compo')
            ->addSelect("IF(rp.idJoueur0=:idCompetiteur, 1, 0) as isPlayer0")
            ->addSelect("IF(rp.idJoueur1=:idCompetiteur, 1, 0) as isPlayer1")
            ->addSelect("IF(rp.idJoueur2=:idCompetiteur, 1, 0) as isPlayer2")
            ->addSelect("IF(rp.idJoueur3=:idCompetiteur, 1, 0) as isPlayer3")
            ->addSelect("IF(rp.idJoueur4=:idCompetiteur, 1, 0) as isPlayer4")
            ->addSelect("IF(rp.idJoueur5=:idCompetiteur, 1, 0) as isPlayer5")
            ->addSelect("IF(rp.idJoueur6=:idCompetiteur, 1, 0) as isPlayer6")
            ->addSelect("IF(rp.idJoueur7=:idCompetiteur, 1, 0) as isPlayer7")
            ->addSelect("IF(rp.idJoueur8=:idCompetiteur, 1, 0) as isPlayer8")
            ->from('App:Competiteur', 'c')
            ->where('rp.idJournee > :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('rp.idJournee <= 7')
            ->andWhere('rp.idEquipe = 2')
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere('rp.idJoueur0 = c.idCompetiteur OR rp.idJoueur1 = c.idCompetiteur OR rp.idJoueur2 = c.idCompetiteur OR rp.idJoueur3 = c.idCompetiteur OR rp.idJoueur4 = c.idCompetiteur OR rp.idJoueur5 = c.idCompetiteur OR rp.idJoueur6 = c.idCompetiteur OR rp.idJoueur7 = c.idCompetiteur OR rp.idJoueur8 = c.idCompetiteur')
            ->andWhere("(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p WHERE (p.idJoueur0 = c.idCompetiteur OR p.idJoueur1 = c.idCompetiteur OR p.idJoueur2 = c.idCompetiteur OR p.idJoueur3 = c.idCompetiteur OR p.idJoueur4 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur6 = c.idCompetiteur OR p.idJoueur7 = c.idCompetiteur OR p.idJoueur8 = c.idCompetiteur) AND p.idEquipe = 1) >= " . $limitePreBrulage)
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
            ->addSelect("IF(rp.idJoueur0=:idCompetiteur, 1, 0) as isPlayer0")
            ->addSelect("IF(rp.idJoueur1=:idCompetiteur, 1, 0) as isPlayer1")
            ->addSelect("IF(rp.idJoueur2=:idCompetiteur, 1, 0) as isPlayer2")
            ->addSelect("IF(rp.idJoueur3=:idCompetiteur, 1, 0) as isPlayer3")
            ->addSelect("IF(rp.idJoueur4=:idCompetiteur, 1, 0) as isPlayer4")
            ->addSelect("IF(rp.idJoueur5=:idCompetiteur, 1, 0) as isPlayer5")
            ->addSelect("IF(rp.idJoueur6=:idCompetiteur, 1, 0) as isPlayer6")
            ->addSelect("IF(rp.idJoueur7=:idCompetiteur, 1, 0) as isPlayer7")
            ->addSelect("IF(rp.idJoueur8=:idCompetiteur, 1, 0) as isPlayer8")
            ->from('App:Competiteur', 'c')
            ->where('rp.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere('rp.idJoueur0 = c.idCompetiteur OR rp.idJoueur1 = c.idCompetiteur OR rp.idJoueur2 = c.idCompetiteur OR rp.idJoueur3 = c.idCompetiteur OR rp.idJoueur4 = c.idCompetiteur OR rp.idJoueur5 = c.idCompetiteur OR rp.idJoueur6 = c.idCompetiteur OR rp.idJoueur7 = c.idCompetiteur OR rp.idJoueur8 = c.idCompetiteur')
            ->getQuery()->getResult();
    }

    /**
     * Récupère la liste des joueurs devant être au plus 1 sélectionnés dans l'équipe
     * @param $idEquipe
     * @return int|mixed|string
     */
    public function getBrulesJ2($idEquipe){
        $composJ1 = $this->createQueryBuilder('rd')
            ->select('(rd.idJoueur0) as joueur0')
            ->addSelect('(rd.idJoueur1) as joueur1')
            ->addSelect('(rd.idJoueur2) as joueur2')
            ->addSelect('(rd.idJoueur3) as joueur3')
            ->addSelect('(rd.idJoueur4) as joueur4')
            ->addSelect('(rd.idJoueur5) as joueur5')
            ->addSelect('(rd.idJoueur6) as joueur6')
            ->addSelect('(rd.idJoueur7) as joueur7')
            ->addSelect('(rd.idJoueur8) as joueur8')
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

    /**
     * @param int $idDeletedCompetiteur
     * @param int $idJoueurColumn
     * @return int|mixed|string
     */
    public function setDeletedCompetiteurToNull(int $idDeletedCompetiteur, int $idJoueurColumn)
    {
        return $this->createQueryBuilder('rp')
            ->update('App\Entity\RencontreParis', 'rp')
            ->set('rp.idJoueur' . $idJoueurColumn, 'NULL')
            ->where('rp.idJoueur' . $idJoueurColumn . ' = :idDeletedCompetiteur')
            ->setParameter('idDeletedCompetiteur', $idDeletedCompetiteur)
            ->getQuery()
            ->execute();
    }
}
