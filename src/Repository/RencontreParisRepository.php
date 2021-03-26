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
     * Liste des compositions où le joueur est brûlés et sélectionnés
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
            if ($i < $nbJoueurs - 1){
                $strP .= ' OR ';
                $strRP .= ' OR ';
            }
            $query = $query->addSelect('IF(rp.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i);
        }
        $query = $query
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
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p, App\Entity\EquipeParis e1 WHERE (' . $strP . ') AND p.idEquipe = e1.idEquipe AND e1.numero < (SELECT MAX(e2.numero) FROM App\Entity\EquipeParis e2)) >= ' . $limitePreBrulage)
            ->getQuery()
            ->getResult();
        return $query;
    }

    /**
     * Liste des sélections où le joueur est sélectionné alors que déclaré indisponible
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
            $query->addSelect('IF(rp.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i);
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
     * @param int $idCompetiteur
     * @param int $idJoueurColumn
     * @return int|mixed|string
     */
    public function setDeletedCompetiteurToNull(int $idCompetiteur, int $idJoueurColumn)
    {
        return $this->createQueryBuilder('rp')
            ->update('App\Entity\RencontreParis', 'rp')
            ->set('rp.idJoueur' . $idJoueurColumn, 'NULL')
            ->where('rp.idJoueur' . $idJoueurColumn . ' = :idDeletedCompetiteur')
            ->setParameter('idDeletedCompetiteur', $idCompetiteur)
            ->getQuery()
            ->execute();
    }
}
