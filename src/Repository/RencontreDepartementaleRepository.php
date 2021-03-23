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
     * Récupère la liste des rencontres
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
     * @return int|mixed|string
     */
    public function getOrderedRencontres(){
        return $this->createQueryBuilder('rd')
            ->leftJoin('rd.idJournee', 'j')
            ->orderBy('j.date')
            ->addOrderBy('rd.idJournee')
            ->addOrderBy('rd.idEquipe')
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
        $query = $this->createQueryBuilder('rd')
            ->select('rd as compo');
        $strP = '';
        $strRD = '';
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $strP .= 'p.idJoueur' .$i . ' = c.idCompetiteur';
            $strRD .= 'rd.idJoueur' .$i . ' = c.idCompetiteur';
            if ($i < $nbJoueurs - 1) $strP .= ' OR ';
            if ($i < $nbJoueurs - 1) $strRD .= ' OR ';
            $query->addSelect("IF(rd.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i . '");
        }
        $query
            ->from('App:Competiteur', 'c')
            ->where('rd.idJournee > :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('rd.idEquipe > :idEquipe')
            ->setParameter('idEquipe', $idEquipe)
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere($strRD)
            ->andWhere("(SELECT COUNT(p.id) FROM App\Entity\RencontreDepartementale p WHERE (' . $strP . ') AND p.idEquipe < (SELECT MAX(e.numero) FROM App\Entity\PriveEquipeDepartementale e)) >= " . $limitePreBrulage)
            ->getQuery()->getResult();
        return $query;
    }

    /**
     * Récupère la liste des joueurs sélectionnés alors que déclarés indisponibles
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
            $query->addSelect('IF(rd.idJoueur' . $i . ' = :idCompetiteur, 1, 0) as isPlayer' . $i . '"');
        }
        $query
            ->from('App:Competiteur', 'c')
            ->where('rd.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('c.idCompetiteur = c.idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere($str)
            ->getQuery()->getResult();
        return $query;
    }

    /**
     * Récupère la liste des joueurs ayant été sélectionnés en J1
     * @param int $idEquipe
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function getBrulesJ2(int $idEquipe, int $nbJoueurs){
        $composJ1 = $this->createQueryBuilder('rd')
            ->select('rd.id');
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $composJ1->addSelect('(rd.idJoueur' . $i . ') as joueur' . $i);
        }
        $composJ1
            ->where('rd.idEquipe < :idEquipe')
            ->andWhere('rd.idJournee = 1')
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
        return $this->createQueryBuilder('rd')
            ->update('App\Entity\RencontreDepartementale', 'rd')
            ->set('rd.idJoueur' . $idJoueurColumn, 'NULL')
            ->where('rd.idJoueur' . $idJoueurColumn . ' = :idDeletedCompetiteur')
            ->setParameter('idDeletedCompetiteur', $idDeletedCompetiteur)
            ->getQuery()
            ->execute();
    }
}
