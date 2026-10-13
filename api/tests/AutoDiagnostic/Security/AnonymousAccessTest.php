<?php

declare(strict_types=1);

namespace App\Tests\AutoDiagnostic\Security;

use App\Tests\AbstractTestCase;
use App\Tests\Trait\CustomerlessAppointmentTrait;
use Symfony\Component\HttpFoundation\Response;

class AnonymousAccessTest extends AbstractTestCase
{
    use CustomerlessAppointmentTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCustomerlessAppointment();
    }

    protected function tearDown(): void
    {
        $this->removeCustomerlessAppointment();
        parent::tearDown();
    }

    public function testAnonymousCannotGetTheAutoDiagnosticOfAnAppointmentWithoutCustomer(): void
    {
        $this->anonymous('GET', sprintf('/auto_diagnostics/%d', $this->customerlessAutoDiagnostic->id));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAnonymousCannotUpdateTheAutoDiagnosticOfAnAppointmentWithoutCustomer(): void
    {
        $this->anonymous('PUT', sprintf('/auto_diagnostics/%d', $this->customerlessAutoDiagnostic->id), ['comment' => 'changed']);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAnonymousCannotDeleteTheAutoDiagnosticOfAnAppointmentWithoutCustomer(): void
    {
        $this->anonymous('DELETE', sprintf('/auto_diagnostics/%d', $this->customerlessAutoDiagnostic->id));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testTheRepairerStillGetsTheAutoDiagnosticOfHisAppointment(): void
    {
        $this->createClientWithUser($this->customerlessAppointment->repairer->owner)->request('GET', sprintf('/auto_diagnostics/%d', $this->customerlessAutoDiagnostic->id));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    private function anonymous(string $method, string $url, array $json = []): void
    {
        self::createClient()->request($method, $url, ['headers' => ['Content-Type' => 'application/json'], 'json' => $json]);
    }
}
