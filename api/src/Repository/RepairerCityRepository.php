<?php

namespace App\Repository;

use App\Entity\RepairerCity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RepairerCity>
 *
 * @method RepairerCity|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepairerCity|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepairerCity[]    findAll()
 * @method RepairerCity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepairerCityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepairerCity::class);
    }

//    /**
//     * @return RepairerCity[] Returns an array of RepairerCity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RepairerCity
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
