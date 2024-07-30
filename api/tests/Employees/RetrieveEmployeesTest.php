<?php

declare(strict_types=1);

namespace App\Tests\Employees;

use App\Entity\Repairer;
use App\Entity\RepairerEmployee;
use App\Entity\User;
use App\Repository\RepairerEmployeeRepository;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @property TranslatorInterface $translator
 */
class RetrieveEmployeesTest extends AbstractTestCase
{
    private RepairerEmployeeRepository $repairerEmployeeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repairerEmployeeRepository = static::getContainer()->get(RepairerEmployeeRepository::class);
    }

    public function testGetRepairerEmployeesAsUser(): void
    {
        $this->createClientAuthAsUser()->request('GET', '/repairer_employees');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetRepairerEmployeeAsUser(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $this->createClientAuthAsUser()->request('GET', sprintf('/repairer_employees/%s', $repairerEmployee->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetRepairerEmployeesAsAdmin(): void
    {
        $this->createClientAuthAsAdmin()->request('GET', '/repairer_employees');
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/contexts/RepairerEmployee',
            '@id' => '/repairer_employees',
            '@type' => 'hydra:Collection',
        ]);
    }

    public function testGetRepairerEmployeeAsAdmin(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $this->createClientAuthAsAdmin()->request('GET', sprintf('/repairer_employees/%s', $repairerEmployee->id));
        $this->assertResponseIsSuccessful();
    }

    public function testGetRepairerEmployeeAsBadBoss(): void
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

        // Boss of repairer 1 try to get an employee of repairer 2
        $this->createClientWithUser($repairer->owner)->request('GET', sprintf('/repairer_employees/%s', $repairerEmployee->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetRepairerEmployeeAsGoodBoss(): void
    {
        // Boss of repairer 1 try to get an employee of repairer 1
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        $this->createClientWithUser($repairerEmployee->repairer->owner)->request('GET', sprintf('/repairer_employees/%s', $repairerEmployee->id));
        $this->assertResponseIsSuccessful();
    }

    public function testGetEmployeesAsBossWithEmployees(): void
    {
        $this->createClientAuthAsBoss()->request('GET', '/repairer_employees');
        $this->assertJsonContains([
            '@context' => '/contexts/RepairerEmployee',
            '@id' => '/repairer_employees',
            '@type' => 'hydra:Collection',
        ]);
    }

    public function testGetEmployeesAsBossWithNoEmployees(): void
    {
        $users = array_reverse(static::getContainer()->get(UserRepository::class)->findAll());

        $firstRandomBoss = null;
        /** @var User $user */
        foreach ($users as $user) {
            if ($user->isBoss() && 'boss@test.com' !== $user->email) {
                $firstRandomBoss = $user;
            }
        }

        if (!$firstRandomBoss) {
            throw new NotFoundHttpException($this->translator->trans('404_notFound.boss', domain: 'validators'));
        }

        // Boss of repairer 1 try to get an employee of repairer 2
        $response = $this->createClientWithCredentials(['email' => $firstRandomBoss->email, 'password' => 'Test1passwordOk!'])->request('GET', '/repairer_employees');
        $this->assertEquals(0, $response->toArray()['hydra:totalItems']);
    }
}
