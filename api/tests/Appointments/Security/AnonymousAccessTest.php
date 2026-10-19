<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Security;

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

    public function testAnonymousCannotGetAnAppointmentWithoutCustomer(): void
    {
        $this->anonymous('GET', sprintf('/appointments/%d', $this->customerlessAppointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAnonymousCannotUpdateAnAppointmentWithoutCustomer(): void
    {
        $this->anonymous('PUT', sprintf('/appointments/%d', $this->customerlessAppointment->id), ['slotTime' => (new \DateTimeImmutable('+2 months'))->format('Y-m-d H:i:s')]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAnonymousCannotDeleteAnAppointmentWithoutCustomer(): void
    {
        $this->anonymous('DELETE', sprintf('/appointments/%d', $this->customerlessAppointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAnonymousCannotMakeATransitionOnAnAppointmentWithoutCustomer(): void
    {
        $this->anonymous('PUT', sprintf('/appointment_transition/%d', $this->customerlessAppointment->id), ['transition' => 'refused']);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAnonymousCannotGetAnAppointmentWithACustomer(): void
    {
        $appointment = self::getContainer()->get('doctrine')->getRepository(\App\Entity\Appointment::class)->findOneBy(['customer' => self::getContainer()->get('doctrine')->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'user1@test.com'])]);

        $this->anonymous('GET', sprintf('/appointments/%d', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testTheRepairerStillManagesAnAppointmentWithoutCustomer(): void
    {
        $repairer = $this->customerlessAppointment->repairer;
        $client = $this->createClientWithUser($repairer->owner);

        $client->request('GET', sprintf('/appointments/%d', $this->customerlessAppointment->id));
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request('DELETE', sprintf('/appointments/%d', $this->customerlessAppointment->id));
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    private function anonymous(string $method, string $url, array $json = []): void
    {
        self::createClient()->request($method, $url, ['headers' => ['Content-Type' => 'application/json'], 'json' => $json]);
    }
}
