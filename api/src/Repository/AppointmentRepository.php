<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Repairer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findFullSlots(Repairer $repairer, ?\DateTimeInterface $start = new \DateTime(), \DateTimeInterface $end = null): array
    {
        if (!$end) {
            $end = new \DateTime();
            $end->add(new \DateInterval('P60D'));
        }

        $query = $this->getEntityManager()->createQuery(<<<DQL
            SELECT a.slotTime AS appointment_time
            FROM {$this->getClassName()} a
            WHERE a.repairer = :repairer
            AND a.slotTime BETWEEN :start_date AND :end_date
            AND a.status in (:excludeStates)
            GROUP BY a.repairer, a.slotTime
            HAVING COUNT(a.id) >= :numberOfSlots
            ORDER BY a.slotTime
        DQL
        )->setParameters([
            'repairer' => $repairer,
            'start_date' => $start,
            'end_date' => $end,
            'numberOfSlots' => $repairer->numberOfSlots ?? 1,
            'excludeStates' => [
                Appointment::PENDING_REPAIRER,
                Appointment::VALIDATED,
            ],
        ]);

        return array_column($query->getArrayResult(), 'appointment_time');
    }

    public function getAppointmentWithoutAutoDiagnostic(): ?Appointment
    {
        return $this->createQueryBuilder('ap')
            ->leftJoin('ap.autoDiagnostic', 'ad')
            ->where('ad IS NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAppointmentCustomersIdsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.customer', 'ac')
            ->select('ac.id')
            ->andWhere('a.repairer = :repairer')
            ->addGroupBy('ac.id')
        ;
    }

    public function getOldOrDelayedAppointments(): array
    {
        $now = new \DateTimeImmutable();
        $back72Hours = $now->sub(new \DateInterval('PT72H'));

        return $this->createQueryBuilder('a')
            ->andWhere('a.slotTime <= :now AND a.status = :pendingRepairer') // Old appointment never accepted
            ->orWhere('a.createdAt IS NOT NULL AND a.createdAt <= :back72Hours AND a.status = :pendingRepairer') // Appointment without response since 72h
            ->setParameter('now', $now)
            ->setParameter('back72Hours', $back72Hours)
            ->setParameter('pendingRepairer', Appointment::PENDING_REPAIRER)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getThreeLastAppointmentsRepairersIds(User $user): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('DISTINCT r.id as repairerId')
            ->leftJoin('a.repairer', 'r')
            ->addSelect('a.id as HIDDEN id')
            ->andWhere('a.customer = :customer')
            ->orderBy('a.id', 'DESC')
            ->setParameter('customer', $user->id)
            ->setMaxResults(3)
        ;

        return array_column($qb->getQuery()->getArrayResult(), 'repairerId');
    }

    public function findOneByCustomerAndUserRepairer(User $customer, User $userRepairer): ?Appointment
    {
        $qb = $this->createQueryBuilder('a')
            ->setMaxResults(1)
            ->where('a.customer = :customer')
            ->setParameter('customer', $customer);

        if ($userRepairer->repairerEmployee) {
            $qb->andWhere('a.repairer = :repairer')
                ->setParameter('repairer', $userRepairer->repairerEmployee->repairer);
        } elseif ($userRepairer->repairers->count() > 0) {
            $condition = '';
            foreach ($userRepairer->repairers as $key => $repairer) {
                if ('' !== $condition) {
                    $condition .= ' OR ';
                }
                $condition .= sprintf('a.repairer = :repairer_%s', $key);
                $qb->setParameter(sprintf('repairer_%s', $key), $repairer);
            }
            $qb->andWhere($condition);
        } else {
            return null;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getNextAppointmentsNotSync(Repairer $repairer): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.repairer = :repairerId')
            ->andWhere('a.googleSync != :true')
            ->andWhere('a.slotTime > :now')
            ->setParameter('repairerId', $repairer->id)
            ->setParameter('true', true)
            ->setParameter('now', new \DateTimeImmutable())
        ;

        return $qb->getQuery()->getResult();
    }
}
