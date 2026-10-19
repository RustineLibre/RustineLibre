<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Security;

use App\Entity\Appointment;
use App\Entity\AutoDiagnostic;
use App\Entity\DiscussionMessage;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * The owner or the employees of a repairer waiting for approval get no access to its appointments,
 * auto-diagnostics and messages.
 */
class PendingRepairerOwnerTest extends AbstractTestCase
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

    private function appointmentOf(string $repairer): Appointment
    {
        $appointment = $this->em()->getRepository(Appointment::class)->findOneBy(['repairer' => $this->scenario[$repairer]]);
        self::assertInstanceOf(Appointment::class, $appointment);

        return $appointment;
    }

    private function autoDiagnosticOn(Appointment $appointment): AutoDiagnostic
    {
        $autoDiagnostic = new AutoDiagnostic();
        $autoDiagnostic->appointment = $appointment;
        $autoDiagnostic->prestation = 'test prestation';
        $this->scenarioPersist($autoDiagnostic);
        $this->em()->flush();

        return $autoDiagnostic;
    }

    private function messageIn(string $discussion): DiscussionMessage
    {
        $message = new DiscussionMessage();
        $message->discussion = $this->scenario[$discussion];
        $message->sender = $this->scenario['customerA'];
        $message->content = 'Hello';
        $this->scenarioPersist($message);
        $this->em()->flush();

        return $message;
    }

    public function testOwnerOfPendingRepairerCannotUpdateAnAppointment(): void
    {
        $appointment = $this->appointmentOf('repairerP');

        $this->createClientWithUser($this->scenario['bossP'])->request('PUT', sprintf('/appointments/%d', $appointment->id), [
            'json' => ['slotTime' => (new \DateTimeImmutable('+2 months'))->format('Y-m-d H:i:s')],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testOwnerOfPendingRepairerCannotDeleteAnAppointment(): void
    {
        $appointment = $this->appointmentOf('repairerP');

        $this->createClientWithUser($this->scenario['bossP'])->request('DELETE', sprintf('/appointments/%d', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testOwnerOfPendingRepairerCannotApplyATransition(): void
    {
        $appointment = $this->appointmentOf('repairerP');

        $this->createClientWithUser($this->scenario['bossP'])->request('PUT', sprintf('/appointment_transition/%d', $appointment->id), [
            'json' => ['transition' => 'cancellation'],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEmployeeOfPendingRepairerCannotApplyATransition(): void
    {
        $appointment = $this->appointmentOf('repairerP');

        $this->createClientWithUser($this->scenario['employeeP'])->request('PUT', sprintf('/appointment_transition/%d', $appointment->id), [
            'json' => ['transition' => 'cancellation'],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testOwnerOfEnabledRepairerCanApplyATransition(): void
    {
        $appointment = $this->appointmentOf('repairerA');

        $this->createClientWithUser($this->scenario['bossA'])->request('PUT', sprintf('/appointment_transition/%d', $appointment->id), [
            'json' => ['transition' => 'cancellation'],
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testOwnerOfPendingRepairerCannotReadAnAutoDiagnostic(): void
    {
        $autoDiagnostic = $this->autoDiagnosticOn($this->appointmentOf('repairerP'));

        $this->createClientWithUser($this->scenario['bossP'])->request('GET', sprintf('/auto_diagnostics/%d', $autoDiagnostic->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testOwnerOfEnabledRepairerCanReadAnAutoDiagnostic(): void
    {
        $autoDiagnostic = $this->autoDiagnosticOn($this->appointmentOf('repairerA'));

        $this->createClientWithUser($this->scenario['bossA'])->request('GET', sprintf('/auto_diagnostics/%d', $autoDiagnostic->id));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testOwnerOfPendingRepairerCannotReadAMessage(): void
    {
        $message = $this->messageIn('discussionP');

        $this->createClientWithUser($this->scenario['bossP'])->request('GET', sprintf('/discussion_messages/%d', $message->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEmployeeOfPendingRepairerCannotPostAMessage(): void
    {
        $this->createClientWithUser($this->scenario['employeeP'])->request('POST', '/discussion_messages', [
            'json' => ['discussion' => sprintf('/discussions/%d', $this->scenario['discussionP']->id), 'content' => 'Hello'],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testOwnerOfEnabledRepairerCanReadAndPostMessages(): void
    {
        $message = $this->messageIn('discussion');
        $client = $this->createClientWithUser($this->scenario['bossA']);

        $client->request('GET', sprintf('/discussion_messages/%d', $message->id));
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request('POST', '/discussion_messages', [
            'json' => ['discussion' => sprintf('/discussions/%d', $this->scenario['discussion']->id), 'content' => 'Hello'],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
}
