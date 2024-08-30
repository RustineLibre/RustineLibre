<?php

namespace App\Tests\Repairer;

use App\Repository\BikeTypeRepository;
use App\Repository\RepairerTypeRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateUserAndRepairerTest extends AbstractTestCase
{
    private BikeTypeRepository $bikeTypeRepository;

    private RepairerTypeRepository $repairerTypeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->bikeTypeRepository = static::getContainer()->get(BikeTypeRepository::class);
        $this->repairerTypeRepository = static::getContainer()->get(RepairerTypeRepository::class);

        //        $this->bikeTypes = static::getContainer()->get(BikeTypeRepository::class)->findAll();
        //        $this->repairerTypes = static::getContainer()->get(RepairerTypeRepository::class)->findAll();
        //
        //        $this->jsonNewRepairerAndUser = [
        //            'firstName' => 'Michel',
        //            'lastName' => 'Michel',
        //            'email' => 'michel@michel.com',
        //            'plainPassword' => 'Test1passwordOk!',
        //            'name' => 'Nouvel atelier',
        //            'street' => 'rue de la justice',
        //            'streetNumber' => '8',
        //            'city' => 'Lille',
        //            'postcode' => '59000',
        //            'bikeTypesSupported' => ['/bike_types/'.$this->bikeTypes[0]->id, '/bike_types/'.$this->bikeTypes[1]->id],
        //            'repairerType' => '/repairer_types/'.$this->repairerTypes[0]->id,
        //            'comment' => 'Bonjour je voudrais rejoindre votre super plateforme',
        //        ];
    }

    public function testPostRepairerAndUserMissingFields(): void
    {
        $jsonMissingFields = $this->generateJsonNewRepairerAndUser();
        unset($jsonMissingFields['city']);
        unset($jsonMissingFields['email']);

        // No need auth
        $response = self::createClient()->request('POST', '/create_user_and_repairer', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $jsonMissingFields,
        ]);
        self::assertResponseStatusCodeSame(422);
    }

    public function testPostRepairerAndUser(): void
    {
        // No need auth
        $response = self::createClient()->request('POST', '/create_user_and_repairer', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $this->generateJsonNewRepairerAndUser(),
        ]);
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = $response->toArray();

        $newRepairerIri = $responseData['@id'];
        $newOwnerIri = $responseData['owner'];

        $this->assertArrayHasKey('owner', $responseData);
        $this->assertNotNull($responseData['owner']);
        $this->assertEquals('Lille', $responseData['city']);
        $this->assertEquals('59000', $responseData['postcode']);
        // Check if multiple repairerTypes are well set
        $this->assertCount(2, $responseData['repairerTypes']);

        // Remove creations for futures tests
        $this->createClientAuthAsAdmin()->request('DELETE', $newRepairerIri);
        $this->createClientAuthAsAdmin()->request('DELETE', $newOwnerIri);
    }

    private function generateJsonNewRepairerAndUser(): array
    {
        $bikeTypes = $this->bikeTypeRepository->findAll();
        $repairerTypes = $this->repairerTypeRepository->findAll();

        return [
            'firstName' => 'Michel',
            'lastName' => 'Michel',
            'email' => sprintf('%s@test.com', bin2hex(random_bytes(5))),
            'plainPassword' => 'Test1passwordOk!',
            'name' => 'Nouvel atelier',
            'street' => 'rue de la justice',
            'streetNumber' => '8',
            'city' => 'Lille',
            'postcode' => '59000',
            'bikeTypesSupported' => [sprintf('/bike_types/%s', $bikeTypes[0]->id), sprintf('/bike_types/%s', $bikeTypes[1]->id)],
            'repairerTypes' => [sprintf('/repairer_types/%s', $repairerTypes[0]->id), sprintf('/repairer_types/%s', $repairerTypes[1]->id)],
            'comment' => 'Bonjour je voudrais rejoindre votre super plateforme',
        ];
    }
}
