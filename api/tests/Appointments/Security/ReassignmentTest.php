<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Security;

use App\Entity\Appointment;
use App\Repairers\Service\UpdateOldFirstSlotAvailableService;
use App\Repository\AppointmentRepository;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReassignmentTest extends AbstractTestCase
{
    private Appointment $appointment;

    public function setUp(): void
    {
        parent::setUp();
        self::getContainer()->get(UpdateOldFirstSlotAvailableService::class)->updateOldFirstSlotAvailable();
        $appointment = self::getContainer()->get(AppointmentRepository::class)->findOneBy([]);
        if (!$appointment instanceof Appointment || null === $appointment->customer) {
            self::fail('Appointment with a customer not found');
        }
        $this->appointment = $appointment;
    }

    /**
     * @return array<string, string>
     */
    private function otherCustomer(): array
    {
        foreach (self::getContainer()->get(UserRepository::class)->findAll() as $user) {
            if ($user->id !== $this->appointment->customer->id && $user->id !== $this->appointment->repairer->owner->id) {
                return ['customer' => sprintf('/users/%d', $user->id)];
            }
        }
        self::fail('No other user found');
    }

    /**
     * @return array<string, string>
     */
    private function otherRepairer(): array
    {
        foreach (self::getContainer()->get(RepairerRepository::class)->findAll() as $repairer) {
            if ($repairer->id !== $this->appointment->repairer->id) {
                return ['repairer' => sprintf('/repairers/%d', $repairer->id)];
            }
        }
        self::fail('No other repairer found');
    }

    /**
     * @param array<string, string> $json
     */
    private function put(\ApiPlatform\Symfony\Bundle\Test\Client $client, array $json): void
    {
        $client->request('PUT', sprintf('/appointments/%d', $this->appointment->id), ['json' => $json]);
    }

    public function testCustomerCannotReassignCustomer(): void
    {
        $this->put($this->createClientWithUser($this->appointment->customer), $this->otherCustomer());
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCustomerCannotReassignRepairer(): void
    {
        $this->put($this->createClientWithUser($this->appointment->customer), $this->otherRepairer());
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossCannotReassignCustomer(): void
    {
        $this->put($this->createClientWithUser($this->appointment->repairer->owner), $this->otherCustomer());
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossCannotReassignRepairer(): void
    {
        $this->put($this->createClientWithUser($this->appointment->repairer->owner), $this->otherRepairer());
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCustomerCanUpdateWithSameRelations(): void
    {
        $this->put($this->createClientWithUser($this->appointment->customer), [
            'customer' => sprintf('/users/%d', $this->appointment->customer->id),
            'repairer' => sprintf('/repairers/%d', $this->appointment->repairer->id),
            'slotTime' => $this->appointment->repairer->firstSlotAvailable->format('Y-m-d H:i:s'),
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAdminCanReassignCustomer(): void
    {
        $this->put($this->createClientAuthAsAdmin(), $this->otherCustomer() + [
            'slotTime' => $this->appointment->repairer->firstSlotAvailable->format('Y-m-d H:i:s'),
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
