<?php

declare(strict_types=1);

namespace App\Tests\Employees;

use App\Entity\Repairer;
use App\Entity\RepairerEmployee;
use App\Repository\RepairerEmployeeRepository;
use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UpdateEmployeeTest extends AbstractTestCase
{
    private RepairerEmployeeRepository $repairerEmployeeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repairerEmployeeRepository = static::getContainer()->get(RepairerEmployeeRepository::class);
    }

    public function testUpdateNoAuth(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $this->createClient()->request('PUT', sprintf('/employee_and_user/%s', $repairerEmployee->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdateAsUser(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $this->createClientAuthAsUser()->request('PUT', sprintf('/employee_and_user/%s', $repairerEmployee->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateAsBadBoss(): void
    {
        /** @var Repairer $repairer */
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);

        /** @var ?RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->createQueryBuilder('re')
            ->where('re.repairer != :repairer')
            ->setParameter('repairer', $repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$repairerEmployee instanceof RepairerEmployee) {
            self::fail('Aucun employé n\a été trouvé pour ce test.');
        }

        $this->createClientWithUser($repairer->owner)->request('PUT', sprintf('/employee_and_user/%s', $repairerEmployee->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testUpdateAsGoodBoss(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $response = $this->createClientWithUser($repairerEmployee->repairer->owner)->request('PUT', sprintf('/employee_and_user/%s', $repairerEmployee->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => $repairerEmployee->employee->email,
                'firstName' => 'Michel',
                'lastName' => 'Michel Michel',
                'enabled' => true,
                'repairer' => sprintf('/repairers/%s', $repairerEmployee->repairer->id),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = $response->toArray();
        $this->assertEquals('Michel', $responseData['employee']['firstName']);
        $this->assertEquals('Michel Michel', $responseData['employee']['lastName']);
        $this->assertTrue($responseData['enabled']);
    }

    /**
     * @return array<string, array{0: array<string, string>}>
     */
    public static function credentialsProvider(): array
    {
        return [
            'email' => [['email' => 'takeover@test.com']],
            'password' => [['plainPassword' => 'Test1passwordOk!']],
        ];
    }

    /**
     * @dataProvider credentialsProvider
     *
     * @param array<string, string> $credentials
     */
    public function testBossCannotChangeCredentialsOfEmployeeAccount(array $credentials): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $employee = $repairerEmployee->employee;
        $email = $employee->email;
        $passwordHash = $employee->password;

        $this->createClientWithUser($repairerEmployee->repairer->owner)->request('PUT', sprintf('/employee_and_user/%s', $repairerEmployee->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'firstName' => 'Michel',
                'lastName' => 'Michel',
                'enabled' => true,
                'repairer' => sprintf('/repairers/%s', $repairerEmployee->repairer->id),
            ] + $credentials,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        static::getContainer()->get('doctrine')->getManager()->clear();
        $reloaded = $this->repairerEmployeeRepository->find($repairerEmployee->id)->employee;
        $this->assertSame($email, $reloaded->email);
        $this->assertSame($passwordHash, $reloaded->password);
    }

    public function testAdminCanChangeEmailOfEmployeeAccount(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $response = $this->createClientAuthAsAdmin()->request('PUT', sprintf('/employee_and_user/%s', $repairerEmployee->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'michel@test.com',
                'firstName' => 'Michel',
                'lastName' => 'Michel',
                'enabled' => true,
                'repairer' => sprintf('/repairers/%s', $repairerEmployee->repairer->id),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals('michel@test.com', $response->toArray()['employee']['email']);
    }

    public function testUpdateAsAdmin(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $response = $this->createClientAuthAsAdmin()->request('PUT', sprintf('/employee_and_user/%s', $repairerEmployee->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'firstName' => 'Michel Michel',
                'lastName' => 'Michel',
                'enabled' => false,
                'repairer' => sprintf('/repairers/%s', $repairerEmployee->repairer->id),
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = $response->toArray();
        $this->assertEquals('Michel Michel', $responseData['employee']['firstName']);
        $this->assertEquals('Michel', $responseData['employee']['lastName']);
        $this->assertFalse($responseData['enabled']);
    }
}
