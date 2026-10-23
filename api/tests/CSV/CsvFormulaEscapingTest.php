<?php

declare(strict_types=1);

namespace App\Tests\CSV;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Tests\AbstractTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Exported cells come from user input: a value that a spreadsheet would evaluate as a formula is neutralized.
 */
class CsvFormulaEscapingTest extends AbstractTestCase
{
    private const FORMULA = '=1+1';

    public function testExportsNeutralizeFormulas(): void
    {
        $appointment = $this->getAppointmentWithCustomerAndOwner();
        $customer = $appointment->customer;
        $owner = $appointment->repairer->owner;
        $repairerId = $appointment->repairer->id;
        $names = [$customer->id => $customer->firstName, $owner->id => $owner->firstName];

        $connection = $this->connection();
        // Plain SQL, restored at the end: the fixture accounts are shared with the other tests.
        foreach (array_keys($names) as $id) {
            $connection->executeStatement('UPDATE "user" SET first_name = ? WHERE id = ?', [self::FORMULA, $id]);
        }

        try {
            foreach (['/export_users_csv', '/export_appointments_csv', '/export_repairers_csv'] as $uri) {
                $csv = $this->createClientAuthAsAdmin(['Accept' => 'text/csv'])->request('GET', $uri)->getContent();
                self::assertResponseIsSuccessful();
                $this->assertFormulaIsNeutralized($csv, $uri);
            }

            $csv = $this->createClientWithUser($owner)->request('GET', sprintf('/export_customers_csv/%d', $repairerId))->getContent();
            self::assertResponseIsSuccessful();
            $this->assertFormulaIsNeutralized($csv, '/export_customers_csv/{id}');
        } finally {
            foreach ($names as $id => $firstName) {
                $this->connection()->executeStatement('UPDATE "user" SET first_name = ? WHERE id = ?', [$firstName, $id]);
            }
        }
    }

    private function assertFormulaIsNeutralized(string $csv, string $uri): void
    {
        self::assertStringContainsString("'".self::FORMULA, $csv, $uri);
        foreach (explode(PHP_EOL, $csv) as $row) {
            self::assertNotContains(self::FORMULA, str_getcsv($row), $uri);
        }
    }

    private function getAppointmentWithCustomerAndOwner(): Appointment
    {
        /** @var AppointmentRepository $repository */
        $repository = self::getContainer()->get(AppointmentRepository::class);
        /** @var Appointment $appointment */
        $appointment = $repository->createQueryBuilder('a')
            ->innerJoin('a.customer', 'c')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.owner', 'o')
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $appointment;
    }

    private function connection(): Connection
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        return $entityManager->getConnection();
    }
}
