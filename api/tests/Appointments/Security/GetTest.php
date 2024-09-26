<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Security;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GetTest extends AbstractTestCase
{
    private AppointmentRepository $appointmentRepository;

    private UserRepository $userRepository;

    private const EXPORT_APPOINTMENTS_CSV_ENDPOINT = '/export_appointments_csv';

    public function setUp(): void
    {
        parent::setUp();
        $this->appointmentRepository = self::getContainer()->get(AppointmentRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testExportCsvAsRoleAdmin(): void
    {
        $response = $this->createClientAuthAsAdmin(['Accept' => 'text/csv'])->request('GET', self::EXPORT_APPOINTMENTS_CSV_ENDPOINT);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/csv; charset=utf-8');

        $rows = explode(PHP_EOL, $response->getContent());

        $header = str_getcsv($rows[0]);
        $expectedHeader = ['Nom', 'Prénom', 'Email', 'Tel', 'Inscription', 'Création_Rdv', 'Rdv', 'Prestation', 'Enseigne'];
        $this->assertEquals($expectedHeader, $header);

        $rowsCount = count($rows) - 2; // -1 for header, -1 for last empty line from csv file
        $appointmentCount = count($this->appointmentRepository->findAll());
        $this->assertEquals($appointmentCount, $rowsCount);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testExportCsvForbidden(): void
    {
        $this->createClientAuthAsBoss(['Accept' => 'text/csv'])->request('GET', self::EXPORT_APPOINTMENTS_CSV_ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientAuthAsUser(['Accept' => 'text/csv'])->request('GET', self::EXPORT_APPOINTMENTS_CSV_ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientAuthAsRepairer(['Accept' => 'text/csv'])->request('GET', self::EXPORT_APPOINTMENTS_CSV_ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientWithCredentials([], ['Accept' => 'text/csv'])->request('GET', self::EXPORT_APPOINTMENTS_CSV_ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testExportCsvUnauthorized(): void
    {
        static::createClient([], ['headers' => ['Accept' => 'text/csv']])->request('GET', self::EXPORT_APPOINTMENTS_CSV_ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testAdminCanGetAllAppointmentsPaginated(): void
    {
        $response = $this->createClientAuthAsAdmin()->request('GET', '/appointments');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Appointment',
            '@id' => '/appointments',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 87,
            'hydra:view' => [
                '@id' => '/appointments?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/appointments?page=1',
                'hydra:last' => '/appointments?page=3',
                'hydra:next' => '/appointments?page=2',
            ],
        ]);

        $response = $response->toArray();

        // ensure that all required properties are returned
        foreach ($response['hydra:member'] as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('@id', $item);
            self::assertArrayHasKey('@type', $item);
            self::assertArrayHasKey('id', $item);
            self::assertArrayHasKey('slotTime', $item);
            self::assertArrayHasKey('createdAt', $item);

            self::assertArrayHasKey('customer', $item);
            $customer = $item['customer'];
            self::assertArrayHasKey('@id', $customer);
            self::assertArrayHasKey('@type', $customer);
            self::assertArrayHasKey('email', $customer);
            self::assertArrayHasKey('lastName', $customer);
            self::assertArrayHasKey('firstName', $customer);
            self::assertArrayHasKey('createdAt', $customer);

            self::assertArrayHasKey('repairer', $item);
            $repairer = $item['repairer'];
            self::assertArrayHasKey('@id', $repairer);
            self::assertArrayHasKey('@type', $repairer);
            self::assertArrayHasKey('name', $repairer);

            if (!isset($item['autoDiagnostic'])) {
                continue;
            }

            self::assertArrayHasKey('autoDiagnostic', $item);
            $autoDiagnostic = $item['autoDiagnostic'];
            self::assertArrayHasKey('@id', $autoDiagnostic);
            self::assertArrayHasKey('@type', $autoDiagnostic);
            self::assertArrayHasKey('prestation', $autoDiagnostic);
        }

        self::assertCount(30, $response['hydra:member']);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testAdminCanFilterAppointmentsBySearchTerm_CustomerEmail(): void
    {
        $searchTerm = 'user1@';
        $response = $this->createClientAuthAsAdmin()->request('GET', "/appointments?search=$searchTerm");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => 17,
        ]);

        $response = $response->toArray();

        foreach ($response['hydra:member'] as $item) {
            self::assertStringContainsStringIgnoringCase($searchTerm, $item['customer']['email']);
        }
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testAdminCanFilterAppointmentsBySearchTerm_CustomerLastName(): void
    {
        $searchTerm = 'Tille';
        $response = $this->createClientAuthAsAdmin()->request('GET', "/appointments?search=$searchTerm");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => 17,
        ]);

        $response = $response->toArray();

        foreach ($response['hydra:member'] as $item) {
            self::assertStringContainsStringIgnoringCase($searchTerm, $item['customer']['lastName']);
        }
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testAdminCanFilterAppointmentsBySearchTerm_CustomerFirstName(): void
    {
        $searchTerm = 'raph';
        $response = $this->createClientAuthAsAdmin()->request('GET', "/appointments?search=$searchTerm");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => 17,
        ]);

        $response = $response->toArray();

        foreach ($response['hydra:member'] as $item) {
            self::assertStringContainsStringIgnoringCase($searchTerm, $item['customer']['firstName']);
        }
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testAdminCanFilterAppointmentsBySearchTerm_RepairerName(): void
    {
        $searchTerm = 'cycl';
        $response = $this->createClientAuthAsAdmin()->request('GET', "/appointments?search=$searchTerm");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => 2,
        ]);

        $response = $response->toArray();

        foreach ($response['hydra:member'] as $item) {
            self::assertStringContainsStringIgnoringCase($searchTerm, $item['repairer']['name']);
        }
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testAdminCanFilterAppointmentsBySearchTerm_AutoDiagnosticPrestation(): void
    {
        $searchTerm = 'problè';
        $response = $this->createClientAuthAsAdmin()->request('GET', "/appointments?search=$searchTerm");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => 3,
        ]);

        $response = $response->toArray();

        foreach ($response['hydra:member'] as $item) {
            self::assertStringContainsStringIgnoringCase($searchTerm, $item['autoDiagnostic']['prestation']);
        }
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testAdminCanFilterAppointmentsBySearchTerm_InRepairerAndAutoDiagnostic(): void
    {
        $searchTerm = 'vélo';
        $this->createClientAuthAsAdmin()->request('GET', "/appointments?search=$searchTerm");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => 28,
            'hydra:member' => [
                [
                    'repairer' => [
                        'name' => 'Le vélo de Belleville',
                    ]
                ],
                [
                    'autoDiagnostic' => [
                        'prestation' => 'Électrifier mon vélo',
                    ]
                ],
            ],
        ]);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testAdminCanGetAllAppointmentsFilteredBySlotTime(): void
    {
        $after = (new \DateTimeImmutable())->format('Y-m-d');
        $before = (new \DateTimeImmutable())->add(new \DateInterval('P1M'))->format('Y-m-d');

        $response = $this->createClientAuthAsAdmin()->request('GET', "/appointments?slotTime[after]=$after&slotTime[before]=$before");

        self::assertResponseIsSuccessful();

        $response = $response->toArray();

        foreach ($response['hydra:member'] as $item) {
            self::assertGreaterThanOrEqual($after, $item['slotTime']);
            self::assertLessThanOrEqual($before, $item['slotTime']);
        }

        self::assertGreaterThanOrEqual(10, count($response['hydra:member']));
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetAllAppointmentsForbidden(): void
    {
        $this->createClientAuthAsBoss()->request('GET', '/appointments');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientAuthAsUser()->request('GET', '/appointments');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientAuthAsRepairer()->request('GET', '/appointments');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientWithCredentials()->request('GET', '/appointments');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanGetOneAppointment(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $this->createClientAuthAsAdmin()->request('GET', sprintf('/appointments/%s', $appointment->id));

        self::assertResponseIsSuccessful();
    }

    public function testCustomerCanGetAllHisAppointments(): void
    {
        /** @var ?Appointment $appointment */
        $appointment = $this->appointmentRepository->findOneBy([]);
        $response = $this->createClientWithUser($appointment->customer)->request('GET', sprintf('/customers/%s/appointments', $appointment->customer->id))->toArray();
        $customers = array_map(static function ($appointment) {
            return $appointment['customer'];
        }, $response['hydra:member']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, array_unique($customers));
    }

    public function testCustomerCanGetOneOfHisAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $response = $this->createClientWithUser($appointment->customer)->request('GET', sprintf('/appointments/%s', $appointment->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(sprintf('/users/%d', $appointment->customer->id), $response['customer']['@id']);
    }

    public function testCustomerCannotGetOtherAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $customer = $this->userRepository->createQueryBuilder('u')
            ->andWhere('u.id != :id')
            ->setParameter('id', $appointment->customer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$customer) {
            self::fail('No customer was found for this test.');
        }

        $this->createClientWithUser($customer)->request('GET', sprintf('/appointments/%s', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRepairerCanGetAllHisAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $response = $this->createClientWithUser($appointment->repairer->owner)->request('GET', sprintf('/repairers/%s/appointments', $appointment->repairer->id))->toArray();
        $repairers = array_map(static function ($appointment) {
            return $appointment['repairer']['@id'];
        }, $response['hydra:member']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, array_unique($repairers));
    }

    public function testRepairerCanGetOneOfHisAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $response = $this->createClientWithUser($appointment->repairer->owner)->request('GET', sprintf('/appointments/%s', $appointment->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(sprintf('/repairers/%d', $appointment->repairer->id), $response['repairer']['@id']);
    }

    public function testRepairerCannotGetOtherAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $boss = $this->userRepository->createQueryBuilder('u')
            ->innerJoin('u.repairers', 'r')
            ->andWhere('r.id != :id')
            ->setParameter('id', $appointment->repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$boss) {
            self::fail('No repairer was found for this test.');
        }

        $this->createClientWithUser($boss)->request('GET', sprintf('/appointments/%s', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRepairerEmployeeCanGetAllHisAppointments(): void
    {
        /** @var Appointment|null $appointment */
        $appointment = $this->appointmentRepository->createQueryBuilder('a')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.repairerEmployees', 'e')
            ->andWhere('e.id IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$appointment) {
            self::fail('No appointment was found for this test.');
        }

        $repairer = $appointment->repairer;
        $employee = $repairer->repairerEmployees->toArray()[0];
        $response = $this->createClientWithUser($employee->employee)->request('GET', sprintf('/repairers/%s/appointments', $appointment->repairer->id))->toArray();
        $repairers = array_map(static function ($appointment) {
            return $appointment['repairer']['@id'];
        }, $response['hydra:member']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, array_unique($repairers));
    }

    public function testRepairerEmployeeCanGetOneOfHisAppointments(): void
    {
        /** @var Appointment|null $appointment */
        $appointment = $this->appointmentRepository->createQueryBuilder('a')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.repairerEmployees', 'e')
            ->andWhere('e.id IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$appointment) {
            self::fail('No appointment was found for this test.');
        }

        $repairer = $appointment->repairer;
        $employee = $repairer->repairerEmployees->toArray()[0];
        $this->createClientWithUser($employee->employee)->request('GET', sprintf('/appointments/%s', $appointment->id));
        self::assertResponseIsSuccessful();
    }

    public function testRepairerEmployeeCannotGetOtherAppointments(): void
    {
        /** @var Appointment|null $appointment */
        $appointment = $this->appointmentRepository->createQueryBuilder('a')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.repairerEmployees', 'e')
            ->andWhere('e.id IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$appointment) {
            self::fail('No appointment was found for this test.');
        }

        $userEmployee = $this->userRepository->createQueryBuilder('u')
            ->innerJoin('u.repairerEmployee', 'e')
            ->innerJoin('e.repairer', 'r')
            ->andWhere('r.id != :id')
            ->setParameter('id', $appointment->repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$userEmployee) {
            self::fail('No repairer employee was found for this test.');
        }

        $this->createClientWithUser($userEmployee)->request('GET', sprintf('/appointments/%s', $appointment->id));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
