<?php

namespace App\Repository;

use App\Entity\Rencontre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rencontre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rencontre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rencontre[]    findAll()
 * @method Rencontre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RencontreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rencontre::class);
    }

    /**
     * Liste des joueurs sélectionnés lors d'une journée
     * @param Rencontre[] $compos
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
     * @param int $type
     * @return int|mixed|string
     */
    public function getRencontres(int $idJournee, int $type){
        return $this->createQueryBuilder('r')
            ->leftJoin('r.idEquipe', 'e')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('r.idJournee = :idJournee')
            ->andWhere('r.idChampionnat = :type')
            ->setParameter('idJournee', $idJournee)
            ->setParameter('type', $type)
            ->orderBy('e.numero')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des rencontres dans le backoffice
     * @return array
     */
    public function getOrderedRencontres(): array
    {
        $query = $this->createQueryBuilder('r')
            ->select('e.numero')
            ->addSelect('j.idJournee')
            ->addSelect('c.nom')
            ->addSelect('j.dateJournee')
            ->addSelect('j.undefined')
            ->addSelect('r.adversaire')
            ->addSelect('r.domicile')
            ->addSelect('r.hosted')
            ->addSelect('d.idDivision')
            ->addSelect('r.reporte')
            ->addSelect('r.exempt')
            ->addSelect('r.dateReport')
            ->addSelect('r.id')
            ->leftJoin('r.idJournee', 'j')
            ->leftJoin('r.idEquipe', 'e')
            ->leftJoin('e.idDivision', 'd')
            ->leftJoin('r.idChampionnat', 'c')
            ->orderBy('c.nom')
            ->addOrderBy('j.dateJournee')
            ->addOrderBy('r.idJournee')
            ->addOrderBy('e.numero')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($query as $key => $item) {
            $querySorted[$item['nom']][$item['numero']][$key] = $item;
        }
        return $querySorted;
    }

    /**
     * Liste des compositions où le joueur est brûlé et sélectionné
     * @param int $idCompetiteur
     * @param int $idJournee
     * @param int $idEquipe
     * @param int $limiteBrulage
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function getSelectedWhenBurnt(int $idCompetiteur, int $idJournee, int $idEquipe, int $limiteBrulage, int $nbJoueurs){
        $query = $this->createQueryBuilder('rp')
            ->select('rp as compo');
        $strP = $strRP = '';
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
            ->andWhere('e.numero > :idEquipe')
            ->setParameter('idEquipe', $idEquipe)
            ->andWhere('e.idDivision IS NOT NULL')
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere($strRP)
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p, App\Entity\EquipeParis e1 WHERE (' . $strP . ') AND p.idEquipe = e1.idEquipe AND p.idJournee <= :idJournee AND e1.idDivision IS NOT NULL AND e1.numero < (SELECT MAX(e2.numero) FROM App\Entity\EquipeParis e2 WHERE e2.idDivision IS NOT NULL)) >= ' . $limiteBrulage)
            ->setParameter('idJournee', $idJournee)
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
            $query = $query->addSelect('IF(rp.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i);
        }
        $query = $query
            ->from('App:Competiteur', 'c')
            ->leftJoin('rp.idEquipe', 'e')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('rp.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('c.idCompetiteur = c.idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere($str)
            ->getQuery()
            ->getResult();
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
            ->update('App\Entity\Rencontre', 'rp')
            ->set('rp.idJoueur' . $idJoueurColumn, 'NULL')
            ->where('rp.idJoueur' . $idJoueurColumn . ' = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $idDivision
     * @return int|mixed|string
     */
    public function getRencontresForDivision(int $idDivision)
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->addSelect('e')
            ->addSelect('d')
            ->leftJoin('r.idEquipe', 'e')
            ->leftJoin('e.idDivision', 'd')
            ->where('e.idDivision = :idDivision')
            ->andWhere('r.idEquipe = e.idEquipe')
            ->setParameter('idDivision', $idDivision)
            ->getQuery()
            ->getResult();
    }

    /**
     * // TODO Tester
     * Réinitialise les rencontres pour une nouvelle phase
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function reset(int $nbJoueurs)
    {
        $query = $this->createQueryBuilder('rp')
            ->update('App\Entity\Rencontre', 'rp');
        for ($i = 0; $i < $nbJoueurs; $i++){
            $query = $query->set('rp.idJoueur' . $i, null);
        }
        $query = $query
            ->set('rp.reporte', false)
            ->set('rp.dateReport', 'j.dateJournee')
            ->set('rp.domicile', true)
            ->set('rp.hosted', false)
            ->set('rp.exempt', false)
            ->set('rp.adversaire', null)
            ->leftJoin('rp.idJournee', 'j')
            ->getQuery()
            ->execute();

        return $query;
    }
}
