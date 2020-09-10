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
            ->where("JSON_VALUE(c.brulageParis, '$.1') >= 3")
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
            ->where("JSON_VALUE(c.brulageParis, '$.1') = 2")
            ->andWhere("JSON_VALUE(c.brulageParis, '$.2') < 3")
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
}
