<?php

namespace App\Repository;

use App\Entity\WebsiteMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebsiteMedia>
 *
 * @method WebsiteMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebsiteMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebsiteMedia[]    findAll()
 * @method WebsiteMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebsiteMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebsiteMedia::class);
    }

    //    /**
    //     * @return WebsiteMedia[] Returns an array of WebsiteMedia objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WebsiteMedia
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
