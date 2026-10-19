<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Security;

use App\Entity\Appointment;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Repairer-side actions on an appointment, a bike or an auto-diagnostic require a relationship
 * with the repairer, not only a staff role.
 */
class StaffRelationshipTest extends AbstractTestCase
{
    use TenantScenarioTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTenantScenario();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTenantScenario();
        parent::tearDown();
    }

    private function appointmentAt(string $repairer, ?string $customer): Appointment
    {
        $appointment = new Appointment();
        $appointment->customer = $customer ? $this->scenario[$customer] : null;
        $appointment->repairer = $this->scenario[$repairer];
        $appointment->slotTime = new \DateTimeImmutable('+1 month');
        $this->scenarioPersist($appointment);
        $this->em()->flush();

        return $appointment;
    }

    /**
     * @param array<string, string> $extra
     */
    private function transition(string $actor, Appointment $appointment, string $transition, array $extra = []): void
    {
        $this->createClientWithUser($this->scenario[$actor])->request('PUT', sprintf('/appointment_transition/%d', $appointment->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['transition' => $transition] + $extra,
        ]);
    }

    public function testBossOfAnotherRepairerCannotValidateHisOwnAppointment(): void
    {
        $appointment = $this->appointmentAt('repairerB', 'bossP');

        $this->transition('bossP', $appointment, 'validated_by_repairer');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossOfAnotherRepairerCannotRefuseHisOwnAppointment(): void
    {
        $appointment = $this->appointmentAt('repairerB', 'bossP');

        $this->transition('bossP', $appointment, 'refused');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossOfAnotherRepairerCannotProposeAnotherSlotOnHisOwnAppointment(): void
    {
        $appointment = $this->appointmentAt('repairerB', 'bossP');

        $this->transition('bossP', $appointment, 'propose_another_slot', ['slotTime' => (new \DateTimeImmutable('+2 months'))->format('Y-m-d H:i:s')]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossOfTheRepairerCanValidateAnAppointment(): void
    {
        $appointment = $this->appointmentAt('repairerB', 'customerA');

        $this->transition('bossB', $appointment, 'validated_by_repairer');

        self::assertResponseIsSuccessful();
    }

    public function testBossOfTheRepairerCanRefuseAnAppointment(): void
    {
        $appointment = $this->appointmentAt('repairerB', 'customerA');

        $this->transition('bossB', $appointment, 'refused');

        self::assertResponseIsSuccessful();
    }

    /**
     * @return list<string>
     */
    private function violationPaths(string $actor, string $bike): array
    {
        $response = $this->createClientWithUser($this->scenario[$actor])->request('POST', '/appointments', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'repairer' => sprintf('/repairers/%d', $this->scenario['repairerA']->id),
                'slotTime' => (new \DateTimeImmutable('+1 month'))->format('Y-m-d H:i:s'),
                'bike' => sprintf('/bikes/%d', $this->scenario[$bike]->id),
            ],
        ]);

        return array_column($response->toArray(false)['violations'] ?? [], 'propertyPath');
    }

    public function testBossCannotAttachTheBikeOfAnUnrelatedUserToAnAppointment(): void
    {
        self::assertContains('bike', $this->violationPaths('bossA', 'bikeB'));
    }

    public function testBossCanAttachTheBikeOfOneOfHisCustomersToAnAppointment(): void
    {
        self::assertNotContains('bike', $this->violationPaths('bossA', 'bikeA'));
    }

    public function testBossOfPendingRepairerCannotAttachTheBikeOfACustomerOfAnotherRepairer(): void
    {
        self::assertContains('bike', $this->violationPaths('bossP', 'bikeB'));
    }

    private function postAutoDiagnostic(string $actor, Appointment $appointment): void
    {
        $this->createClientWithUser($this->scenario[$actor])->request('POST', '/auto_diagnostics', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'appointment' => sprintf('/appointments/%d', $appointment->id),
                'prestation' => 'test prestation',
            ],
        ]);
    }

    public function testBossOfAnotherRepairerCannotCreateAnAutoDiagnosticOnTheAppointment(): void
    {
        $appointment = $this->appointmentAt('repairerA', 'customerA');

        $this->postAutoDiagnostic('bossB', $appointment);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testBossOfAnotherRepairerCannotCreateAnAutoDiagnosticOnACustomerlessAppointment(): void
    {
        $appointment = $this->appointmentAt('repairerA', null);

        $this->postAutoDiagnostic('bossB', $appointment);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testBossOfTheRepairerCanCreateAnAutoDiagnosticOnACustomerlessAppointment(): void
    {
        $appointment = $this->appointmentAt('repairerA', null);

        $this->postAutoDiagnostic('bossA', $appointment);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
}
