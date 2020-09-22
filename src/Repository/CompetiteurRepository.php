<?php

namespace App\Repository;

use App\Entity\Competiteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Competiteur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competiteur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competiteur[]    findAll()
 * @method Competiteur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetiteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competiteur::class);
    }

    /**
     * @param $idJournee
     * @param $tableDispo
     * @return mixed[]
     * @throws DBALException
     */
    public function findJoueursNonDeclares($idJournee, $tableDispo)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT prive_competiteur.nom, prive_competiteur.id_competiteur"
            . " FROM prive_competiteur"
            . " WHERE prive_competiteur.id_competiteur NOT IN (SELECT DISTINCT id_competiteur"
            . " FROM prive_" . $tableDispo . ""
            . " WHERE id_journee = " . $idJournee . ")"
            . " ORDER BY prive_competiteur.nom";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Liste des compétiteurs n'ayant pas rempli toutes leurs dispos pour le championnat départemental
     * @param string $type
     * @return int|mixed|string
     * @throws DBALException
     */
    public function findAllDispos(string $type)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT avatar, prive_competiteur.id_competiteur, prive_competiteur.nom, prive_journee_" . $type . ".id_journee, prive_journee_" . $type . ".date,"
            .   " (SELECT disponibilite FROM prive_disponibilite_" . $type . " WHERE prive_competiteur.id_competiteur=prive_disponibilite_" . $type . ".id_competiteur AND prive_disponibilite_" . $type . ".id_journee=prive_journee_" . $type . ".id_journee) AS disponibilite,"
            .   " (SELECT id_disponibilite FROM prive_disponibilite_" . $type . " WHERE prive_competiteur.id_competiteur=prive_disponibilite_" . $type . ".id_competiteur AND prive_disponibilite_" . $type . ".id_journee=prive_journee_" . $type . ".id_journee) AS id_disponibilite"
            .   " FROM prive_competiteur, prive_journee_" . $type . ""
            .   " ORDER BY prive_competiteur.nom, prive_journee_" . $type . ".id_journee";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récapitulatif de toutes les disponibilités
     * @param string $type
     * @return mixed[]
     * @throws DBALException
     */
    public function findAllDisposRecapitulatif(string $type){
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT avatar, prive_competiteur.nom,";

        for ($i = 1; $i <= 7; $i++){
            $sql .= " (SELECT disponibilite FROM prive_disponibilite_" . $type . ""
                .   " WHERE prive_competiteur.id_competiteur = prive_disponibilite_" . $type . ".id_competiteur AND id_journee = " . $i . ") AS j" . $i;
            if ($i < 7) $sql .= ",";
        }

        $sql .= " FROM prive_competiteur ORDER BY prive_competiteur.nom";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param int $idJournee
     * @return int|mixed|string
     */
    public function getCompetiteurBrulageDepartemental(int $idJournee){
        $brulages = $this->createQueryBuilder('c')
            ->select('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) AS E1')
            ->addSelect('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) AS E2')
            ->addSelect('(SELECT COUNT(p3.id) FROM App\Entity\RencontreDepartementale p3 WHERE (p3.idJoueur1 = c.idCompetiteur OR p3.idJoueur2 = c.idCompetiteur OR p3.idJoueur3 = c.idCompetiteur OR p3.idJoueur4 = c.idCompetiteur) AND p3.idJournee < :idJournee AND p3.idEquipe = 3) AS E3')
            ->addSelect('c.nom')
            ->setParameter('idJournee', $idJournee)
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            $allBrulage[$brulage["nom"]] = ["E1" => $brulage["E1"], "E2"=>$brulage["E2"], "E3" => $brulage["E3"]];
        }

        return $allBrulage;
    }

    /**
     * @param int $idJournee
     * @return array
     */
    public function getCompetiteurBrulageParis(int $idJournee){
        $brulages = $this->createQueryBuilder('c')
            ->select('(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p WHERE (p.idJoueur1 = c.idCompetiteur OR p.idJoueur2 = c.idCompetiteur OR p.idJoueur3 = c.idCompetiteur OR p.idJoueur4 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur6 = c.idCompetiteur OR p.idJoueur7 = c.idCompetiteur OR p.idJoueur8 = c.idCompetiteur OR p.idJoueur9 = c.idCompetiteur) AND p.idJournee < :idJournee AND p.idEquipe = 1) AS E1')
            ->addSelect('c.nom')
            ->setParameter('idJournee', $idJournee)
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            $allBrulage[$brulage["nom"]] = ["E1" => $brulage["E1"]];
        }

        return $allBrulage;
    }
}