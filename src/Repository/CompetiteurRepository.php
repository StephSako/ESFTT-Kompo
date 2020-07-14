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
     * @param $idTeam
     * @return int|mixed|string
     */
    public function findBurnPlayersDepartementale($idTeam)
    {
        $query = $this
            ->createQueryBuilder('c');

        switch ($idTeam) {
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
     * @param $idTeam
     * @return int|mixed|string
     */
    public function findBurnPlayersParis($idTeam)
    {
        $query = $this
            ->createQueryBuilder('c')
            ->where("JSON_VALUE(c.brulageParis, '$.1') >= 2");

        return $query
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get almost burnt players in departementale
     * @param $idTeam
     * @return int|mixed|string
     */
    public function findAlmostBurnPlayersDepartementale($idTeam)
    {
        $query = $this->createQueryBuilder('c')
            ->where("JSON_VALUE(c.brulageDepartemental, '$." . $idTeam . "') = 1")
            ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2");

        switch ($idTeam) {
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
        $query = $this->createQueryBuilder('c')
            ->where("JSON_VALUE(c.brulageParis, '$.1') = 1")
            ->andWhere("JSON_VALUE(c.brulageParis, '$.2') < 2");
        return $query
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }
}
