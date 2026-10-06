<?php

declare(strict_types=1);

namespace App\Tests\Bike\Security;

use App\Entity\Bike;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\BikeRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\BikeTrait;
use Symfony\Component\HttpFoundation\Response;

class GetTest extends AbstractTestCase
{
    use BikeTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bikeRepository = self::getContainer()->get(BikeRepository::class);
    }

    public function testAdminCanGetBike(): void
    {
        $bike = $this->getBike();
        $response = $this->createClientAuthAsAdmin()->request('GET', sprintf('/bikes/%d', $bike->id))->toArray();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('id', $response);
        self::assertArrayHasKey('owner', $response);
        self::assertArrayHasKey('brand', $response);
        self::assertArrayHasKey('bikeType', $response);
        self::assertArrayHasKey('name', $response);
        self::assertArrayHasKey('description', $response);
    }

    public function testOwnerCanGetHisBike(): void
    {
        $bike = $this->getBike();
        $this->createClientWithUser($bike->owner)->request('GET', sprintf('/bikes/%d', $bike->id));
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testUserCannotGetOtherBike(): void
    {
        $bike = $this->getBike();
        $this->createClientAuthAsUser()->request('GET', sprintf('/bikes/%d', $bike->id));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testOwnerGetCollectionOfHisBikes(): void
    {
        $bike = $this->getBikeFromAnUser();
        $response = $this->createClientWithUser($bike->owner)->request('GET', '/bikes')->toArray();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('hydra:member', $response);
        self::assertArrayHasKey('hydra:totalItems', $response);

        foreach ($response['hydra:member'] as $bikeResponse) {
            self::assertArrayHasKey('id', $bikeResponse);
            self::assertArrayHasKey('owner', $bikeResponse);
            self::assertSame(sprintf('/users/%d', $bike->owner->id), $bikeResponse['owner']['@id']);
            self::assertArrayHasKey('brand', $bikeResponse);
            self::assertArrayHasKey('bikeType', $bikeResponse);
            self::assertArrayHasKey('name', $bikeResponse);
            self::assertArrayHasKey('description', $bikeResponse);
        }
    }

    public function testAdminGetCollectionOfBikes(): void
    {
        $response = $this->createClientAuthAsAdmin()->request('GET', '/bikes')->toArray();

        self::assertResponseStatusCodeSame(200);
        self::assertArrayHasKey('hydra:member', $response);
        self::assertArrayHasKey('hydra:totalItems', $response);

        $owners = [];
        foreach ($response['hydra:member'] as $bikeResponse) {
            self::assertArrayHasKey('id', $bikeResponse);
            self::assertArrayHasKey('owner', $bikeResponse);
            $owners[] = $bikeResponse['owner']['@id'];
            self::assertArrayHasKey('brand', $bikeResponse);
            self::assertArrayHasKey('bikeType', $bikeResponse);
            self::assertArrayHasKey('name', $bikeResponse);
            self::assertArrayHasKey('description', $bikeResponse);
        }

        // Check that all bikes are not from the same owner
        self::assertGreaterThan(1, count(array_unique($owners)));
    }

    public function testBossGetCollectionOnlyContainsBikesOfHisCustomers(): void
    {
        $boss = $this->getBoss();
        $response = $this->createClientWithUser($boss)->request('GET', '/bikes')->toArray();

        self::assertResponseIsSuccessful();
        $expected = array_filter($this->bikeRepository->findAll(), fn (Bike $bike) => $this->isOwnBikeOrCustomerBike($bike, $boss));
        self::assertSame(count($expected), $response['hydra:totalItems']);
        self::assertLessThan(count($this->bikeRepository->findAll()), $response['hydra:totalItems']);
        foreach ($response['hydra:member'] as $bikeResponse) {
            self::assertTrue($this->isOwnBikeOrCustomerBike($this->bikeRepository->find($bikeResponse['id']), $boss));
        }
    }

    public function testBossCanGetBikesOfHisCustomer(): void
    {
        $boss = $this->getBoss();
        $customer = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user1@test.com']);
        $response = $this->createClientWithUser($boss)->request('GET', sprintf('/bikes?owner=%d', $customer->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $response['hydra:totalItems']);
        self::assertSame(count($this->bikeRepository->findBy(['owner' => $customer])), $response['hydra:totalItems']);
    }

    public function testBossCannotGetBikesOfAUserWhoIsNotHisCustomer(): void
    {
        $boss = $this->getBoss();
        $bikes = array_filter($this->bikeRepository->findAll(), fn (Bike $bike) => !$this->isOwnBikeOrCustomerBike($bike, $boss));
        $owner = array_shift($bikes)->owner;
        $response = $this->createClientWithUser($boss)->request('GET', sprintf('/bikes?owner=%d', $owner->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }

    private function getBoss(): User
    {
        return self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'boss@test.com']);
    }

    private function isOwnBikeOrCustomerBike(Bike $bike, User $user): bool
    {
        return $bike->owner->id === $user->id
            || null !== self::getContainer()->get(AppointmentRepository::class)->findOneByCustomerAndUserRepairer($bike->owner, $user);
    }
}
