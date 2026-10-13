<?php

declare(strict_types=1);

namespace App\Tests\Repairer;

use App\Entity\Repairer;
use App\Entity\User;
use App\Repository\BikeTypeRepository;
use App\Repository\RepairerTypeRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Someone who applies to become a repairer must not reach other users' data before an administrator
 * approves the repairer, and gets only the customers of that repairer afterwards.
 */
class ApplicantAccessTest extends AbstractTestCase
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

    public function testApplicantOfThePublicRegistrationReachesNoCustomerData(): void
    {
        $email = sprintf('applicant-%s@tenant-scenario.test', bin2hex(random_bytes(4)));
        $response = self::createClient()->request('POST', '/create_user_and_repairer', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'firstName' => 'Applicant',
                'lastName' => 'Scenario',
                'email' => $email,
                'plainPassword' => 'Test1passwordOk!',
                'name' => 'Applicant workshop',
                'street' => 'rue de la justice',
                'streetNumber' => '8',
                'city' => 'Lille',
                'postcode' => '59000',
                'bikeTypesSupported' => [sprintf('/bike_types/%s', $this->firstBikeTypeId())],
                'repairerTypes' => [sprintf('/repairer_types/%s', self::getContainer()->get(RepairerTypeRepository::class)->findOneBy([])->id)],
                'comment' => 'Hello',
            ],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->trackCreatedRepairer($response->toArray()['id'], $email);

        $applicant = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
        $applicant->emailConfirmed = true;
        $this->em()->flush();

        $this->assertReachesNoCustomerData($applicant);
    }

    public function testCustomerWhoCreatesARepairerReachesNoDataOfOtherCustomers(): void
    {
        $customer = $this->scenario['customerB'];
        $response = $this->createClientWithUser($customer)->request('POST', '/repairers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'Customer workshop',
                'description' => 'Hello',
                'mobilePhone' => '0720596321',
                'street' => 'rue de la clé',
                'streetNumber' => '8',
                'city' => 'Lille',
                'postcode' => '59000',
                'latitude' => '50.62544631958008',
                'longitude' => '3.0352721214294434',
                'country' => 'France',
                'bikeTypesSupported' => [sprintf('/bike_types/%s', $this->firstBikeTypeId())],
            ],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->trackCreatedRepairer($response->toArray()['id']);

        $this->assertReachesNoCustomerData($customer);
    }

    public function testRepairerGivesAccessToItsCustomersOnlyOnceApproved(): void
    {
        $bikeUrl = sprintf('/bikes/%d', $this->scenario['bikeA']->id);
        $boss = $this->scenario['bossP'];

        $this->createClientWithUser($boss)->request('GET', $bikeUrl);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientAuthAsAdmin()->request('PUT', sprintf('/repairers/%d', $this->scenario['repairerP']->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['enabled' => true],
        ]);
        self::assertResponseIsSuccessful();

        $this->createClientWithUser($boss)->request('GET', $bikeUrl);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    private function assertReachesNoCustomerData(User $user): void
    {
        $client = $this->createClientWithUser($user);
        $otherBikeId = $this->scenario['bikeA']->id;

        $client->request('GET', sprintf('/bikes/%d', $otherBikeId));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $client->request('GET', sprintf('/maintenances/%d', $this->scenario['maintenanceA']->id));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        self::assertNotContains($otherBikeId, array_column($client->request('GET', '/bikes')->toArray()['hydra:member'], 'id'));
        self::assertNotContains($this->scenario['maintenanceA']->id, array_column($client->request('GET', '/maintenances')->toArray()['hydra:member'], 'id'));
        self::assertNotContains($this->scenario['discussion']->id, array_column($client->request('GET', '/discussions')->toArray()['hydra:member'], 'id'));
    }

    private function firstBikeTypeId(): int
    {
        return self::getContainer()->get(BikeTypeRepository::class)->findOneBy([])->id;
    }

    /**
     * Makes the cleanup remove what the test created through the API (repairer first, then its owner).
     */
    private function trackCreatedRepairer(int $repairerId, ?string $ownerEmail = null): void
    {
        $em = $this->em();
        if (null !== $ownerEmail) {
            $this->tenantScenarioEntities[] = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => $ownerEmail]);
        }
        $this->tenantScenarioEntities[] = $em->find(Repairer::class, $repairerId);
    }
}
