<?php

namespace App\Repository;

use App\Entity\Rencontre;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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
     * Liste des compositions où le joueur est brûlé et sélectionné dans de futures compositions
     * @param int $idCompetiteur
     * @param int $idJournee
     * @param int $limiteBrulage
     * @param int $nbJoueurs
     * @param int $type
     * @return int|mixed|string
     */
    public function getSelectedWhenBurnt(int $idCompetiteur, int $idJournee, int $limiteBrulage, int $nbJoueurs, int $type){
        $query = $this->createQueryBuilder('r')
            ->select('r as compo');
        $strP = $strRP = '';
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $strP .= 'p.idJoueur' .$i . ' = c.idCompetiteur';
            $strRP .= 'r.idJoueur' .$i . ' = c.idCompetiteur';
            if ($i < $nbJoueurs - 1){
                $strP .= ' OR ';
                $strRP .= ' OR ';
            }
            $query = $query->addSelect('IF(r.idJoueur' . $i . ' = :idCompetiteur, ' . $idCompetiteur . ', 0) as isPlayer' . $i);
        }
        return $query
            ->from('App:Competiteur', 'c')
            ->leftJoin('r.idEquipe', 'e')
            ->where('r.idJournee > :idJournee')
            ->andWhere('e.idChampionnat = :idChampionnat')
            ->andWhere('e.idDivision IS NOT NULL')
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->andWhere($strRP)

            /** Nombre de matches joués dans les équipes supèrieures depuis le début à aujourd'hui */
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\Rencontre p, App\Entity\Equipe e1 ' .
                       'WHERE (' . $strP . ') ' .
                       'AND p.idEquipe = e1.idEquipe ' .
                       'AND p.idJournee <= :idJournee ' .
                       'AND p.idChampionnat = :idChampionnat ' .
                       'AND e1.idDivision IS NOT NULL ' .
                       'AND e1.numero < e.numero) >= :limite')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->setParameter('limite', $limiteBrulage)
            ->setParameter('idJournee', $idJournee)
            ->setParameter('idChampionnat', $type)
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des sélections où le joueur est sélectionné alors que déclaré indisponible
     * @param int $idCompetiteur
     * @param int $idJournee
     * @param int $nbJoueurs
     * @param int $type
     * @return int|mixed|string
     */
    public function getSelectedWhenIndispo(int $idCompetiteur, int $idJournee, int $nbJoueurs, int $type){
        $query = $this->createQueryBuilder('r')
            ->select('r as compo');
        $str = '';
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $str .= 'r.idJoueur' .$i . ' = c.idCompetiteur';
            if ($i < $nbJoueurs - 1) $str .= ' OR ';
            $query = $query->addSelect('IF(r.idJoueur' . $i . ' = :idCompetiteur, ' . $idCompetiteur . ', 0) as isPlayer' . $i);
        }
        return $query
            ->from('App:Competiteur', 'c')
            ->leftJoin('r.idEquipe', 'e')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('r.idJournee = :idJournee')
            ->andWhere('r.idChampionnat = :idChampionnat')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('c.idCompetiteur = c.idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->setParameter('idChampionnat', $type)
            ->andWhere($str)
            ->getQuery()
            ->getResult();
    }

    /**
     * Réinitialise les rencontres pour une nouvelle phase
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function reset(int $nbJoueurs)
    {
        $query = $this->createQueryBuilder('r')->update('App\Entity\Rencontre', 'r');
        for ($i = 0; $i < $nbJoueurs; $i++){
            $query = $query->set('r.idJoueur' . $i, null);
        }

        return $query
            ->set('r.reporte', false)
            ->set('r.dateReport', 'j.dateJournee')
            ->set('r.domicile', null)
            ->set('r.villeHost', false)
            ->set('r.exempt', false)
            ->set('r.adversaire', null)
            ->leftJoin('r.idJournee', 'j')
            ->getQuery()
            ->execute();
    }

    /**
     * Retourne le nombre maximal de joueurs d'une journée
     * @param int $idJournee
     * @return array
     * @throws NonUniqueResultException
     */
    public function getNbJoueursMaxJournee(int $idJournee): array
    {
        return $this->createQueryBuilder('r')
            ->select('MAX(d.nbJoueurs) as nbMaxJoueurs')
            ->leftJoin('r.idEquipe', 'e')
            ->leftJoin('e.idDivision', 'd')
            ->where('r.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les sélections d'un joueur pour les dates ultèrieures à aujourd'hui inclus
     * @param int $idJoueur
     * @param int $nbMaxJoueurs
     * @param bool $inFutureCompos Récupérer les sélections de futures journées
     * @return array|string|null
     */
    public function getSelectionInChampCompos(int $idJoueur, int $nbMaxJoueurs, bool $inFutureCompos): array {
        $str = '';
        for ($i = 0; $i < $nbMaxJoueurs; $i++) {
            $str .= 'r.idJoueur' . $i . ' = :idJoueur';
            if ($i < $nbMaxJoueurs - 1) $str .= ' OR ';
        }

        $query = $this->createQueryBuilder('r')
            ->select('r')
            ->leftJoin('r.idJournee', 'j')
            ->leftJoin('r.idChampionnat', 'ch')
            ->where($str);
            if ($inFutureCompos) $query = $query->andWhere("j.dateJournee >= DATE('" . (new DateTime())->format('Y-m-d') . "')");

        return $query->setParameter('idJoueur', $idJoueur)
        ->getQuery()
        ->getResult();
    }

    /**
     * Retourne la liste des IDs des Journées ayant des Rencontres reportées
     * @param int $idChampionnat
     * @return array
     */
    public function getJourneesWithReportedRencontres(int $idChampionnat): array
    {
        $rencontres = $this->createQueryBuilder('r')
            ->where('r.reporte = true')
            ->andWhere('r.idChampionnat = :idChampionnat')
            ->setParameter('idChampionnat', $idChampionnat)
            ->getQuery()
            ->getResult();

        return [
            'ids' => array_map(function (Rencontre $r) { return $r->getIdJournee()->getIdJournee(); }, $rencontres),
            'rencontres' => $rencontres
        ];
    }
}
