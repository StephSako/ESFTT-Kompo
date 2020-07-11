<?php

namespace App\Repository;

use App\Entity\Competiteur;
use App\Entity\FirstPhase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FirstPhase|null find($id, $lockMode = null, $lockVersion = null)
 * @method FirstPhase|null findOneBy(array $criteria, array $orderBy = null)
 * @method FirstPhase[]    findAll()
 * @method FirstPhase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirstPhaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FirstPhase::class);
    }

    /**
     * @param FirstPhase $idJournee
     * @return int|mixed|string
     */
    public function findJournee($idJournee)
    {
        return $this->createQueryBuilder('fp')
            ->leftJoin('fp.idJournee', 'j')
            ->where('fp.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get selected players for a specific journee
     * @param $idJournee
     * @return int|mixed|string
     * @throws DBALException
     */
    public function findJourneeSelectedPlayers($idJournee)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT id_joueur_1, id_joueur_2, id_joueur_3, id_joueur_4"
                . " FROM phase_1"
                . " WHERE id_journee = " . $idJournee;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
