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
     * @return mixed[]
     * @throws DBALException
     */
    public function findJoueursNonDeclares($idJournee)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT competiteur.nom"
            . " FROM competiteur"
            . " WHERE competiteur.id_competiteur NOT IN (SELECT DISTINCT id_competiteur"
										. " FROM disponibilite"
                                        . " WHERE id_journee = " . $idJournee . ")"
            . " ORDER BY competiteur.nom";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get burnt players for a specific team
     * @param $idTeam
     * @return int|mixed|string
     */
    public function findBurnPlayers($idTeam)
    {
        $query = $this
            ->createQueryBuilder('c')
            ->where("JSON_VALUE(c.brulage, '$.1') >= 2");

        switch ($idTeam) {
            case 3:
                $query = $query
                    ->orWhere("JSON_VALUE(c.brulage, '$.2') >= 2");
                break;
            case 4:
                $query = $query
                    ->orWhere("JSON_VALUE(c.brulage, '$.2') >= 2")
                    ->orWhere("JSON_VALUE(c.brulage, '$.3') >= 2");
                break;
        }

        return $query
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get almost burnt players
     * @param $idTeam
     * @return int|mixed|string
     */
    public function findAlmostBurnPlayers($idTeam)
    {
        dump($idTeam);
        $query = $this->createQueryBuilder('c')
            ->where("JSON_VALUE(c.brulage, '$." . $idTeam . "') = 1")
            ->andWhere("JSON_VALUE(c.brulage, '$.1') < 2");

        switch ($idTeam) {
            case 3:
                $query = $query
                    ->andWhere("JSON_VALUE(c.brulage, '$.2') < 2");
                break;
            case 4:
                $query = $query
                    ->andWhere("JSON_VALUE(c.brulage, '$.2') < 2")
                    ->andWhere("JSON_VALUE(c.brulage, '$.3') < 2");
                break;
        }
        return $query
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }
}
