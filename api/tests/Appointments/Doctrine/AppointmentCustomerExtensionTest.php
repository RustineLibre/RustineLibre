<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Doctrine;

use App\Repository\AppointmentRepository;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\AppointmentTrait;

class AppointmentCustomerExtensionTest extends AbstractTestCase
{
    use AppointmentTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->appointmentRepository = self::getContainer()->get(AppointmentRepository::class);
    }

    public function testCustomerGetOnlyHisAppointments(): void
    {
        $appointment = $this->getAppointment();
        $response = $this->createClientWithUser($appointment->customer)->request('GET', sprintf('/customers/%s/appointments', $appointment->customer->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, count($response['hydra:member']));

        foreach ($response['hydra:member'] as $result) {
            self::assertSame(sprintf('/users/%d', $appointment->customer->id), $result['customer']);
        }
    }
}
