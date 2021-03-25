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
     * Liste des joueurs sélectionnés lors d'une journée
     * @param RencontreParis[] $compos
     * @return array
     */
    public function getSelectedPlayers(array $compos): array
    {
        $selectedPlayers = [];
        foreach ($compos as $compo){
            if ($compo->getIdEquipe()->getIdDivision()) $selectedPlayers = array_merge($selectedPlayers, $compo->getListSelectedPlayers());
        }
        return array_map(function($joueur){ return $joueur->getIdCompetiteur(); }, $selectedPlayers);
    }

    /**
     * Liste des rencontres
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
     * Liste des rencontres dans le backoffice
     * @return int|mixed|string
     */
    public function getOrderedRencontres(){
        return $this->createQueryBuilder('rp')
            ->leftJoin('rp.idJournee', 'j')
            ->leftJoin('rp.idEquipe', 'e')
            ->orderBy('j.date')
            ->addOrderBy('rp.idJournee')
            ->addOrderBy('e.numero')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des joueurs sélectionnés alors que brûlés
     * @param int $idCompetiteur
     * @param int $idJournee
     * @param int $idEquipe
     * @param int $limitePreBrulage
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function getSelectedWhenBurnt(int $idCompetiteur, int $idJournee, int $idEquipe, int $limitePreBrulage, int $nbJoueurs){
        $query = $this->createQueryBuilder('rp')
            ->select('rp as compo');
        $strP = '';
        $strRP = '';
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $strP .= 'p.idJoueur' .$i . ' = c.idCompetiteur';
            $strRP .= 'rp.idJoueur' .$i . ' = c.idCompetiteur';
            if ($i < $nbJoueurs - 1) $strP .= ' OR ';
            if ($i < $nbJoueurs - 1) $strRP .= ' OR ';
            $query->addSelect("IF(rp.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i . '");
        }
        $query
            ->from('App:Competiteur', 'c')
            ->leftJoin('rp.idEquipe', 'e')
            ->where('rp.idJournee > :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('e.numero > :idEquipe')
            ->setParameter('idEquipe', $idEquipe)
            ->andWhere('e.idDivision IS NOT NULL')
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere($strRP)
            ->andWhere("(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p LEFT JOIN App\Entity\RencontreParis e ON p.idEquipe = e.idEquipe WHERE (' . $strP . ') AND e.numero < (SELECT MAX(e.numero) FROM App\Entity\PriveParis e)) >= " . $limitePreBrulage)
            ->getQuery()
            ->getResult();
        return $query;
    }

    /**
     * Liste des joueurs sélectionnés alors que déclarés indisponibles
     * @param int $idCompetiteur
     * @param int $idJournee
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function getSelectedWhenIndispo(int $idCompetiteur, int $idJournee, int $nbJoueurs){
        $query = $this->createQueryBuilder('rp')
            ->select('rp as compo');
        $str = '';
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $str .= 'rp.idJoueur' .$i . ' = c.idCompetiteur';
            if ($i < $nbJoueurs - 1) $str .= ' OR ';
            $query->addSelect('IF(rp.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i . '"');
        }
        $query
            ->from('App:Competiteur', 'c')
            ->leftJoin('rp.idEquipe', 'e')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('rp.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('c.idCompetiteur = c.idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere($str)
            ->getQuery()->getResult();
        return $query;
    }

    /**
     * Liste des joueurs ayant été sélectionnés en J1
     * @param int $idEquipe
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function getBrulesJ2(int $idEquipe, int $nbJoueurs){
        $composJ1 = $this->createQueryBuilder('rp')
            ->select('rp.id');
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $composJ1->addSelect('(rp.idJoueur' . $i . ') as joueur' . $i);
        }
        $composJ1
            ->leftJoin('rp.idEquipe', 'e')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('e.numero < :idEquipe')
            ->andWhere('rp.idJournee = 1')
            ->setParameter('idEquipe', $idEquipe)
            ->getQuery()
            ->getResult();

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
