<?php

declare(strict_types=1);

namespace App\Tests\Employees;

use App\Entity\RepairerEmployee;
use App\Entity\User;
use App\Repository\RepairerEmployeeRepository;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateEmployeeTest extends AbstractTestCase
{
    private RepairerRepository $repairerRepository;

    private RepairerEmployeeRepository $repairerEmployeeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repairerRepository = static::getContainer()->get(RepairerRepository::class);
        $this->repairerEmployeeRepository = static::getContainer()->get(RepairerEmployeeRepository::class);
    }

    public function testCreateEmployeeNoAuth(): void
    {
        $jsonRequest = $this->generateJsonNewEmployee();

        $this->createClient()->request('POST', '/repairer_employees', ['json' => $jsonRequest]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateEmployeeAsUser(): void
    {
        $jsonRequest = $this->generateJsonNewEmployee();

        $this->createClientAuthAsUser()->request('POST', '/repairer_employees', ['json' => $jsonRequest]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateEmployeeAsAdmin(): void
    {
        $jsonRequest = $this->generateJsonNewEmployee();

        $response = $this->createClientAuthAsAdmin()->request('POST', '/repairer_employees', ['json' => $jsonRequest]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = $response->toArray();
        $this->assertEquals('RepairerEmployee', $responseData['@type']);
        $this->assertEquals($jsonRequest['repairer'], $responseData['repairer']['@id']);
        $this->assertArrayHasKey('employee', $responseData);
        $this->assertArrayHasKey('email', $responseData['employee']);
        $this->assertArrayHasKey('lastName', $responseData['employee']);
        $this->assertArrayHasKey('firstName', $responseData['employee']);
    }

    public function testCannotCreateEmployeeIfRepairerIsNotProvided(): void
    {
        $jsonRequest = $this->generateJsonNewEmployee();

        // Does not provide a repairer, should inject it automatically
        unset($jsonRequest['repairer']);

        /** @var User $currentBoss */
        $currentBoss = $this->getObjectByClassNameAndValues(UserRepository::class, ['email' => 'boss@test.com']);

        $this->createClientAuthAsBoss()->request('POST', '/repairer_employees', ['json' => $jsonRequest]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRemoveEmployeeNotAuth(): void
    {
        $this->createClient()->request('DELETE', sprintf('/repairer_employees/%s', $this->repairerEmployeeRepository->findOneBy([])->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRemoveEmployeeAsUser(): void
    {
        $this->createClientAuthAsUser()->request('DELETE', sprintf('/repairer_employees/%s', $this->repairerEmployeeRepository->findOneBy([])->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRemoveEmployeeAsBadBoss(): void
    {
        /** @var ?RepairerEmployee $repairerEmployee5 */
        $repairerEmployee = $this->repairerEmployeeRepository->createQueryBuilder('re')
            ->innerJoin('re.repairer', 'r')
            ->innerJoin('r.owner', 'o')
            ->where('o.email != :email')
            ->setParameter('email', 'boss@test.com')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $repairerEmployee) {
            self::fail('Aucun employé n\'a été trouvé pour ce test.');
        }

        $this->createClientAuthAsBoss()->request('DELETE', sprintf('/repairer_employees/%s', $repairerEmployee->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRemoveEmployeeAsBoss(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = static::getContainer()->get(RepairerEmployeeRepository::class)->findOneBy([]);
        $this->createClientWithUser($repairerEmployee->repairer->owner)->request('DELETE', sprintf('/repairer_employees/%s', $repairerEmployee->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testRemoveEmployeeAsAdmin(): void
    {
        $repairerEmployee = static::getContainer()->get(RepairerEmployeeRepository::class)->findOneBy([]);
        $this->createClientAuthAsAdmin()->request('DELETE', sprintf('/repairer_employees/%s', $repairerEmployee->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function generateJsonNewEmployee(): array
    {
        return [
            'email' => sprintf('%s@mail.com', bin2hex(random_bytes(5))),
            'plainPassword' => 'Test1passwordOk!',
            'firstName' => 'Michel',
            'lastName' => 'Michel',
            'repairer' => sprintf('/repairers/%s', $this->repairerRepository->findOneBy([])->id),
        ];
    }
}
