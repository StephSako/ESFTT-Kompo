<?php

namespace App\Repository;

use App\Entity\Journee;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Journee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Journee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Journee[]    findAll()
 * @method Journee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JourneeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Journee::class);
    }

    /**
     * @return array
     */
    public function getAllJournees(): array
    {
        $query = $this->createQueryBuilder('j')
            ->select('j.idJournee')
            ->addSelect('j.dateJournee')
            ->addSelect('j.undefined')
            ->addSelect('c.nom')
            ->leftJoin('j.idChampionnat', 'c')
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($query as $key => $item) {
            $querySorted[$item['nom']][$key] = $item;
        }
        return $querySorted;
    }

    /**
     * Avoir la date la plus lointaine d'un championnat
     * @param int $type
     * @return int|mixed|string
     */
    public function findEarlistDate(int $type): DateTime
    {
        $query = $this->createQueryBuilder('j')
            ->select('j.dateJournee')
            ->where('j.idChampionnat = :type')
            ->setParameter('type', $type)
            ->orderBy('j.dateJournee', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
        return $query[0]['dateJournee'];
    }
}
