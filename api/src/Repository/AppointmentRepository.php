<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Repairer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 *
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointment[]    findAll()
 * @method Appointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function save(Appointment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Appointment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findFullSlots(Repairer $repairer, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $query = $this->getEntityManager()->createQuery(<<<DQL
            SELECT a.slotTime AS appointment_time
            FROM {$this->getClassName()} a
            WHERE a.repairer = :repairer
            AND a.slotTime BETWEEN :start_date AND :end_date
            GROUP BY a.repairer, a.slotTime
            ORDER BY a.slotTime
        DQL
        )->setParameters([
            'repairer' => $repairer,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        return array_column($query->getArrayResult(), 'appointment_time');
    }
}