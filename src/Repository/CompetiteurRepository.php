<?php

namespace App\Repository;

use App\Entity\Competiteur;
use App\Entity\EquipeDepartementale;
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
        $sql = "SELECT competiteur.nom, competiteur.id_competiteur"
            . " FROM competiteur"
            . " WHERE competiteur.id_competiteur NOT IN (SELECT DISTINCT id_competiteur"
										. " FROM " . $tableDispo . ""
                                        . " WHERE id_journee = " . $idJournee . ")"
            . " ORDER BY competiteur.nom";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get burnt players for a specific team in departementale
     * @param EquipeDepartementale $team
     * @return int|mixed|string
     */
    public function findBurnPlayersDepartementale($team)
    {
        $query = $this
            ->createQueryBuilder('c');

        switch ($team->getIdEquipe()) {
            case 2:
                $query = $query
                    ->where("JSON_VALUE(c.brulageDepartemental, '$.1') >= 2");
                break;
            case 3:
                $query = $query
                    ->where("JSON_VALUE(c.brulageDepartemental, '$.1') >= 2")
                    ->orWhere("JSON_VALUE(c.brulageDepartemental, '$.2') >= 2");
                break;
            case 4:
                $query = $query
                    ->where("JSON_VALUE(c.brulageDepartemental, '$.1') >= 2")
                    ->orWhere("JSON_VALUE(c.brulageDepartemental, '$.2') >= 2")
                    ->orWhere("JSON_VALUE(c.brulageDepartemental, '$.3') >= 2");
                break;
        }

        return $query
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get burnt players for a specific team in paris
     * @return int|mixed|string
     */
    public function findBurnPlayersParis()
    {
        return $this->createQueryBuilder('c')
            ->where("JSON_VALUE(c.brulageParis, '$.1') >= 2")
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get almost burnt players in departementale
     * @param EquipeDepartementale $team
     * @return int|mixed|string
     */
    public function findAlmostBurnPlayersDepartementale($team)
    {
        $query = $this->createQueryBuilder('c')
            ->where("JSON_VALUE(c.brulageDepartemental, '$." . $team->getIdEquipe() . "') = 1")
            ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2");

        switch ($team->getIdEquipe()) {
            case 3:
                $query = $query
                    ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2");
                break;
            case 4:
                $query = $query
                    ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2")
                    ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.3') < 2");
                break;
        }

        return $query
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get almost burnt players in paris
     * @return int|mixed|string
     */
    public function findAlmostBurnPlayersParis()
    {
        return $this->createQueryBuilder('c')
            ->where("JSON_VALUE(c.brulageParis, '$.1') = 1")
            ->andWhere("JSON_VALUE(c.brulageParis, '$.2') < 2")
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des compétiteurs n'ayant pas rempli toutes leurs dispos pour le championnat départemental
     * @param string $type
     * @return int|mixed|string
     * @throws DBALException
     */
    public function findAllDispos($type)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT competiteur.id_competiteur, competiteur.nom, journee_" . $type . ".id_journee, journee_" . $type . ".date,"
            .   " (SELECT disponibilite FROM disponibilite_" . $type . " WHERE competiteur.id_competiteur=disponibilite_" . $type . ".id_competiteur AND disponibilite_" . $type . ".id_journee=journee_" . $type . ".id_journee) AS disponibilite,"
            .   " (SELECT id_disponibilite FROM disponibilite_" . $type . " WHERE competiteur.id_competiteur=disponibilite_" . $type . ".id_competiteur AND disponibilite_" . $type . ".id_journee=journee_" . $type . ".id_journee) AS id_disponibilite"
            .   " FROM competiteur, journee_" . $type . ""
            .   " ORDER BY competiteur.nom, journee_" . $type . ".id_journee";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
