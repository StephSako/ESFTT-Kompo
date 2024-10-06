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
            ->addSelect('c.idChampionnat')
            ->leftJoin('j.idChampionnat', 'c')
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($query as $key => $item) {
            if (!array_key_exists($item['nom'], $querySorted)) $querySorted[$item['nom']] = [];
            if (!array_key_exists('idChampionnat', $querySorted[$item['nom']])) $querySorted[$item['nom']]['idChampionnat'] = $item['idChampionnat'];
            if (!array_key_exists('journees', $querySorted[$item['nom']])) $querySorted[$item['nom']]['journees'] = [];
            $querySorted[$item['nom']]['journees'][$key] = $item;
        }
        return $querySorted;
    }

    /**
     * Avoir la date la plus lointaine d'un championnat
     * @param int $idChampionnat
     * @return int|mixed|string
     */
    public function findEarliestDate(int $idChampionnat): DateTime
    {
        $query = $this->createQueryBuilder('j')
            ->select('j.dateJournee')
            ->where('j.idChampionnat = :idChampionnat')
            ->setParameter('idChampionnat', $idChampionnat)
            ->orderBy('j.dateJournee', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
        return $query[0]['dateJournee'];
    }
}
