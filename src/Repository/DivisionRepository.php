<?php

namespace App\Repository;

use App\Entity\Division;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Division|null find($id, $lockMode = null, $lockVersion = null)
 * @method Division|null findOneBy(array $criteria, array $orderBy = null)
 * @method Division[]    findAll()
 * @method Division[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DivisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Division::class);
    }

    public function getAllDivisions(): array
    {
        $query = $this->createQueryBuilder('d')
            ->select('d.longName')
            ->addSelect('d.shortName')
            ->addSelect('c.nom')
            ->addSelect('d.nbJoueurs')
            ->addSelect('d.idDivision')
            ->addSelect('c.idChampionnat')
            ->leftJoin('d.idChampionnat', 'c')
            ->orderBy('c.nom')
            ->addOrderBy('d.nbJoueurs', 'DESC')
            ->addOrderBy('d.longName')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($query as $key => $item) {
            $querySorted[$item['nom']][$key] = $item;
        }
        return $querySorted;
    }
}
