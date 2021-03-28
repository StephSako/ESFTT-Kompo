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
     * Liste des joueurs sélectionnés lors d'une journée
     * @param RencontreDepartementale[] $compos
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
    public function getRencontresDepartementales(int $idJournee){
        return $this->createQueryBuilder('rd')
            ->leftJoin('rd.idEquipe', 'e')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('rd.idJournee = :idJournee')
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
        return $this->createQueryBuilder('rd')
            ->leftJoin('rd.idJournee', 'j')
            ->leftJoin('rd.idEquipe', 'e')
            ->orderBy('j.date')
            ->addOrderBy('rd.idJournee')
            ->addOrderBy('e.numero')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des compositions où le joueur est brûlé et sélectionné
     * @param int $idCompetiteur
     * @param int $idJournee
     * @param int $idEquipe
     * @param int $limitePreBrulage
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function getSelectedWhenBurnt(int $idCompetiteur, int $idJournee, int $idEquipe, int $limitePreBrulage, int $nbJoueurs){
        $query = $this->createQueryBuilder('rd')
            ->select('rd as compo');
        $strP = '';
        $strRD = '';
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $strP .= 'p.idJoueur' .$i . ' = c.idCompetiteur';
            $strRD .= 'rd.idJoueur' .$i . ' = c.idCompetiteur';
            if ($i < $nbJoueurs - 1){
                $strP .= ' OR ';
                $strRD .= ' OR ';
            }
            $query = $query->addSelect('IF(rd.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i);
        }
        $query = $query
            ->from('App:Competiteur', 'c')
            ->leftJoin('rd.idEquipe', 'e')
            ->where('rd.idJournee > :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('e.numero > :idEquipe')
            ->setParameter('idEquipe', $idEquipe)
            ->andWhere('e.idDivision IS NOT NULL')
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere($strRD)
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\RencontreDepartementale p, App\Entity\EquipeDepartementale e1 WHERE (' . $strP . ') AND p.idEquipe = e1.idEquipe AND e1.numero < (SELECT MAX(e2.numero) FROM App\Entity\EquipeDepartementale e2)) >= ' . $limitePreBrulage)
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
        $query = $this->createQueryBuilder('rd')
            ->select('rd as compo');
        $str = '';
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $str .= 'rd.idJoueur' .$i . ' = c.idCompetiteur';
            if ($i < $nbJoueurs - 1) $str .= ' OR ';
            $query = $query->addSelect('IF(rd.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i);
        }
        $query = $query
            ->from('App:Competiteur', 'c')
            ->leftJoin('rd.idEquipe', 'e')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('rd.idJournee = :idJournee')
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
        return $this->createQueryBuilder('rd')
            ->update('App\Entity\RencontreDepartementale', 'rd')
            ->set('rd.idJoueur' . $idJoueurColumn, 'NULL')
            ->where('rd.idJoueur' . $idJoueurColumn . ' = :idDeletedCompetiteur')
            ->setParameter('idDeletedCompetiteur', $idCompetiteur)
            ->getQuery()
            ->execute();
    }
}
