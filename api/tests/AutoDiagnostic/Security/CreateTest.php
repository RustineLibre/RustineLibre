<?php

declare(strict_types=1);

namespace App\Tests\AutoDiagnostic\Security;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\AppointmentTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateTest extends AbstractTestCase
{
    use AppointmentTrait;

    /** @var Appointment[] */
    protected array $appointments = [];

    private AppointmentRepository $appointmentRepository;

    private TranslatorInterface $translator;

    public function setUp(): void
    {
        parent::setUp();

        $this->appointmentRepository = static::getContainer()->get(AppointmentRepository::class);
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
    }

    public function testUserCanCreateAutoDiagnostic(): void
    {
        $appointment = $this->appointmentRepository->getAppointmentWithoutAutoDiagnostic();

        $this->createClientWithUser($appointment->customer)->request('POST', '/auto_diagnostics', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'appointment' => sprintf('/appointments/%d', $appointment->id),
                'prestation' => 'test prestation',
            ],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testUserDisconnectedCannotCreateAutoDiagnostic(): void
    {
        $appointment = $this->appointmentRepository->getAppointmentWithoutAutoDiagnostic();

        $this->createClient()->request('POST', '/auto_diagnostics', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'appointment' => sprintf('/appointments/%d', $appointment->id),
                'prestation' => 'test prestation',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUserCannotCreateAutoDiagnosticIfNotOwner(): void
    {
        $appointment = $this->appointmentRepository->getAppointmentWithoutAutoDiagnostic();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->createQueryBuilder('u')
            ->where('u.id != :customer')
            ->andWhere('CAST(u.roles as TEXT) NOT LIKE :admin')
            ->andWhere('CAST(u.roles as TEXT) NOT LIKE :boss')
            ->andWhere('CAST(u.roles as TEXT) NOT LIKE :employee')
            ->setParameter('customer', $appointment->customer)
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->setParameter('boss', '%ROLE_BOSS%')
            ->setParameter('employee', '%ROLE_EMPLOYEE%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $appointment) {
            self::fail('Appointment not found');
        }

        $this->createClientWithUser($user)->request('POST', '/auto_diagnostics', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'appointment' => sprintf('/appointments/%d', $appointment->id),
                'prestation' => 'test prestation',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => sprintf('appointment: %s', $this->translator->trans('autoDiagnostic.appointment.owner', domain: 'validators')),
        ]);
    }
}
