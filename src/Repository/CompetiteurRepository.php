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
     * @return int|mixed|string
     */
    public function findBurnPlayers()
    {
        return $this->createQueryBuilder('p')
            ->where("JSON_VALUE(p.brulage, '$.1') >= 2")
            ->orWhere("JSON_VALUE(p.brulage, '$.2') >= 2")
            ->orWhere("JSON_VALUE(p.brulage, '$.3') >= 2")
            ->orderBy('p.nom')
            ->getQuery()
            ->getResult();
    }
}
