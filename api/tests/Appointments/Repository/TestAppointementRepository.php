<?php

namespace App\Tests\Appointments\Repository;

use App\Entity\Appointment;
use App\Entity\Repairer;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\BikeTypeRepository;
use App\Repository\RepairerEmployeeRepository;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class TestAppointementRepository extends AbstractTestCase
{
    public AppointmentRepository $appointmentRepository;
    public UserRepository $userRepository;
    public RepairerRepository $repairerRepository;
    public RepairerEmployeeRepository $repairerEmployeeRepository;

    public EntityManagerInterface $em;

    public function setUp(): void
    {
        parent::setUp();
        $this->repairerRepository = self::getContainer()->get(RepairerRepository::class);
        $this->repairerEmployeeRepository = self::getContainer()->get(RepairerEmployeeRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->appointmentRepository = self::getContainer()->get(AppointmentRepository::class);
        $this->em = self::getContainer()->get('doctrine')->getManager();
    }

    public function testFindOneByCustomerAndUserRepairerWhenUserIsBoss(): void
    {
        /** @var Repairer $repairer */
        $repairer = $this->repairerRepository->findOneBy([]);
        $customer = $this->userRepository->createQueryBuilder('u')
            ->innerJoin(Appointment::class, 'a', Join::WITH, 'a.customer = u.id')
            ->innerJoin('a.repairer', 'r')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_boss')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_employee')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_admin')
            ->andWhere('r.id NOT IN (:repairersIds)')
            ->setParameter('repairersIds', array_map(static fn (Repairer $repairer): int => $repairer->id, $repairer->owner->repairers->toArray()))
            ->setParameter('role_boss', sprintf('%%%s%%', User::ROLE_BOSS))
            ->setParameter('role_employee', sprintf('%%%s%%', User::ROLE_EMPLOYEE))
            ->setParameter('role_admin', sprintf('%%%s%%', User::ROLE_ADMIN))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$customer) {
            self::fail('No customer was found for this test.');
        }

        self::assertNull($this->appointmentRepository->findOneByCustomerAndUserRepairer($customer, $repairer->owner));

        $client = $this->createClientWithUser($customer);
        $slots = $client->request('GET', sprintf('/repairer_get_slots_available/%d', $repairer->id))->toArray();
        $slotTime = sprintf('%s %s', array_key_first($slots), $slots[array_key_first($slots)][0]);
        $client->request('POST', '/appointments', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $repairer->id),
                'slotTime' => \DateTimeImmutable::createFromFormat('Y-m-d H:i', $slotTime)->format('Y-m-d H:i:s'),
            ],
        ])->toArray();
        self::assertResponseIsSuccessful();

        $fetchedAppointment = $this->appointmentRepository->findOneByCustomerAndUserRepairer($customer, $repairer->owner);
        self::assertNotNull($fetchedAppointment);
    }

    public function testFindOneByCustomerAndUserRepairerWhenUserIsBossAndHeHasSeveralRepairers(): void
    {
        $repairer = $this->repairerRepository->findOneBy([]);
        $boss = $repairer->owner;

        if (2 > $boss->repairers->count()) {
            $bikeTypes = static::getContainer()->get(BikeTypeRepository::class)->findAll();
            $this->createClientWithUser($boss)->request('POST', '/repairers', [
                'json' => [
                    'name' => 'Chez Gérard',
                    'description' => 'On aime réparer des trucs',
                    'mobilePhone' => '0720596321',
                    'street' => 'rue de la clé',
                    'streetNumber' => '8',
                    'city' => 'Lille',
                    'postcode' => '59000',
                    'latitude' => '50.62544631958008',
                    'longitude' => '3.0352721214294434',
                    'country' => 'France',
                    'bikeTypesSupported' => [
                        sprintf('/bike_types/%s', $bikeTypes[0]->id),
                        sprintf('/bike_types/%s', $bikeTypes[1]->id),
                    ],
                ],
            ]);
            self::assertResponseIsSuccessful();
        }

        $boss = $this->userRepository->findOneBy(['id' => $boss->id]);
        $customer = $this->userRepository->createQueryBuilder('u')
            ->innerJoin(Appointment::class, 'a', Join::WITH, 'a.customer = u.id')
            ->innerJoin('a.repairer', 'r')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_boss')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_employee')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_admin')
            ->andWhere('r.id NOT IN (:repairersIds)')
            ->setParameter('repairersIds', array_map(static fn (Repairer $repairer): int => $repairer->id, $boss->repairers->toArray()))
            ->setParameter('role_boss', sprintf('%%%s%%', User::ROLE_BOSS))
            ->setParameter('role_employee', sprintf('%%%s%%', User::ROLE_EMPLOYEE))
            ->setParameter('role_admin', sprintf('%%%s%%', User::ROLE_ADMIN))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$customer) {
            self::fail('No customer was found for this test.');
        }

        self::assertNull($this->appointmentRepository->findOneByCustomerAndUserRepairer($customer, $repairer->owner));

        $client = $this->createClientWithUser($customer);
        $slots = $client->request('GET', sprintf('/repairer_get_slots_available/%d', $repairer->id))->toArray();
        $slotTime = sprintf('%s %s', array_key_first($slots), $slots[array_key_first($slots)][0]);
        $client->request('POST', '/appointments', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $repairer->id),
                'slotTime' => \DateTimeImmutable::createFromFormat('Y-m-d H:i', $slotTime)->format('Y-m-d H:i:s'),
            ],
        ])->toArray();
        self::assertResponseIsSuccessful();

        $fetchedAppointment = $this->appointmentRepository->findOneByCustomerAndUserRepairer($customer, $repairer->owner);
        self::assertNotNull($fetchedAppointment);
    }

    public function testFindOneByCustomerAndUserRepairerWhenUserIsEmployee(): void
    {
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $customer = $this->userRepository->createQueryBuilder('u')
            ->innerJoin(Appointment::class, 'a', Join::WITH, 'a.repairer != :repairer')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_boss')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_employee')
            ->andWhere('CAST(u.roles AS TEXT) NOT LIKE :role_admin')
            ->setParameter('repairer', $repairerEmployee->repairer)
            ->setParameter('role_boss', sprintf('%%%s%%', User::ROLE_BOSS))
            ->setParameter('role_employee', sprintf('%%%s%%', User::ROLE_EMPLOYEE))
            ->setParameter('role_admin', sprintf('%%%s%%', User::ROLE_ADMIN))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$customer) {
            self::fail('No customer was found for this test.');
        }

        self::assertNull($this->appointmentRepository->findOneByCustomerAndUserRepairer($customer, $repairerEmployee->employee));

        $client = $this->createClientWithUser($customer);
        $slots = $client->request('GET', sprintf('/repairer_get_slots_available/%d', $repairerEmployee->repairer->id))->toArray();
        $slotTime = sprintf('%s %s', array_key_first($slots), $slots[array_key_first($slots)][0]);
        $client->request('POST', '/appointments', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $repairerEmployee->repairer->id),
                'slotTime' => \DateTimeImmutable::createFromFormat('Y-m-d H:i', $slotTime)->format('Y-m-d H:i:s'),
            ],
        ])->toArray();
        self::assertResponseIsSuccessful();

        $fetchedAppointment = $this->appointmentRepository->findOneByCustomerAndUserRepairer($customer, $repairerEmployee->employee);
        self::assertNotNull($fetchedAppointment);
    }
}
