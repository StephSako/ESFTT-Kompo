<?php

namespace App\Repository;

use App\Entity\Division;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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

    /**
     * @return array
     */
    public function getAllDivisions(): array
    {
        $query = $this->createQueryBuilder('d')
            ->select('d.longName')
            ->addSelect('d.shortName')
            ->addSelect('COUNT(e) as equipes')
            ->addSelect('c.nom')
            ->addSelect('d.nbJoueurs')
            ->addSelect('d.idDivision')
            ->addSelect('c.idChampionnat')
            ->leftJoin('d.idChampionnat', 'c')
            ->leftJoin('d.equipes', 'e')
            ->groupBy('d.idDivision')
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

    /**
     * @return array
     * @throws NonUniqueResultException
     */
    public function getNbJoueursMax(): array
    {
        return $this->createQueryBuilder('d')
            ->select('MAX(d.nbJoueurs) as nbMaxJoueurs')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
