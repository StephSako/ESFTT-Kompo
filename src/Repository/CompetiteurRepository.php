<?php

namespace App\Repository;

use App\Entity\Competiteur;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @param string $type
     * @return mixed[]
     */
    public function findJoueursNonDeclares($idJournee, string $type)
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.idCompetiteur')
            ->addSelect('c.nom')
            ->where("c.idCompetiteur NOT IN (SELECT DISTINCT IDENTITY(d.idCompetiteur) FROM App\Entity\Disponibilite" . ucfirst($type) . " d WHERE d.idJournee = " . $idJournee . ")")
            ->andWhere('c.visitor <> true')
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récapitulatif de toutes les disponibilités dans la modale
     * @param string $type
     * @return int|mixed|string
     */
    public function findAllDisposRecapitulatif(string $type){
        $result = $this->createQueryBuilder('c')
            ->select('c.avatar')
            ->addSelect('c.nom');

        for ($i = 1; $i <= 7; $i++) {
            $result = $result->addSelect("(SELECT dt" . $i . ".disponibilite FROM App\Entity\Disponibilite" . $type . " dt" . $i . " WHERE c.idCompetiteur = dt" . $i . ".idCompetiteur AND dt" . $i . ".idJournee = " . $i . ") AS j" . $i);
        }

        $result = $result->where('c.visitor <> true')
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Brûlages en championnat départemental
     * @param int $idJournee
     * @return int|mixed|string
     */
    public function getBrulagesDepartemental(int $idJournee){
        $brulages = $this->createQueryBuilder('c')
            ->select('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) AS E1')
            ->addSelect('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) AS E2')
            ->addSelect('(SELECT COUNT(p3.id) FROM App\Entity\RencontreDepartementale p3 WHERE (p3.idJoueur1 = c.idCompetiteur OR p3.idJoueur2 = c.idCompetiteur OR p3.idJoueur3 = c.idCompetiteur OR p3.idJoueur4 = c.idCompetiteur) AND p3.idJournee < :idJournee AND p3.idEquipe = 3) AS E3')
            ->addSelect('c.nom')
            ->addSelect('c.idCompetiteur')
            ->where('c.visitor <> true')
            ->setParameter('idJournee', $idJournee)
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            $allBrulage[$brulage["nom"]] = ["E1" => $brulage["E1"], "E2"=>$brulage["E2"], "E3" => $brulage["E3"], "idCompetiteur" => $brulage["idCompetiteur"]];
        }

        return $allBrulage;
    }

    /**
     * Brûlages en championnat de Paris
     * @param int $idJournee
     * @return array
     */
    public function getBrulagesParis(int $idJournee){
        $brulages = $this->createQueryBuilder('c')
            ->select('(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p WHERE (p.idJoueur1 = c.idCompetiteur OR p.idJoueur2 = c.idCompetiteur OR p.idJoueur3 = c.idCompetiteur OR p.idJoueur4 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur6 = c.idCompetiteur OR p.idJoueur7 = c.idCompetiteur OR p.idJoueur8 = c.idCompetiteur OR p.idJoueur9 = c.idCompetiteur) AND p.idJournee < :idJournee AND p.idEquipe = 1) AS E1')
            ->addSelect('c.nom')
            ->addSelect('c.idCompetiteur')
            ->where('c.visitor <> true')
            ->setParameter('idJournee', $idJournee)
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            $allBrulage[$brulage["nom"]] = ["E1" => $brulage["E1"], "idCompetiteur" => $brulage["idCompetiteur"]];
        }

        return $allBrulage;
    }

    /**
     * BACK-OFFICE : liste de toutes les disponibilités
     * @param string $type
     * @return int|mixed|string
     */
    public function findAllDisponibilites(string $type)
    {
        return $this->createQueryBuilder('c')
            ->select('c.avatar')
            ->addSelect('c.idCompetiteur')
            ->addSelect('c.nom')
            ->addSelect('c.classement_officiel')
            ->addSelect('c.licence')
            ->addSelect('j.idJournee')
            ->addSelect('j.undefined')
            ->addSelect("(SELECT d1.idDisponibilite FROM App\Entity\Disponibilite" . ucfirst($type) . " d1 WHERE c.idCompetiteur = d1.idCompetiteur AND d1.idJournee = j.idJournee) AS idDisponibilite")
            ->addSelect("(SELECT d2.disponibilite FROM App\Entity\Disponibilite" . ucfirst($type) . " d2 WHERE c.idCompetiteur = d2.idCompetiteur AND d2.idJournee = j.idJournee) AS disponibilite")
            ->addSelect('j.date')
            ->from('App:Journee' . ucfirst($type), 'j')
            ->where('c.visitor <> true')
            ->orderBy('c.nom')
            ->addOrderBy('j.idJournee')
            ->getQuery()
            ->getResult();
    }
}