<?php

namespace App\Tests\Repairer;

use App\Repository\BikeTypeRepository;
use App\Repository\RepairerTypeRepository;
use App\Tests\AbstractTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class CreateUserAndRepairerTest extends AbstractTestCase
{
    use RefreshDatabaseTrait;

    private array $jsonNewRepairerAndUser = [];
    private array $bikeTypes = [];
    private array $repairerTypes = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->bikeTypes = static::getContainer()->get(BikeTypeRepository::class)->findAll();
        $this->repairerTypes = static::getContainer()->get(RepairerTypeRepository::class)->findAll();

        $this->jsonNewRepairerAndUser = [
            'firstName' => 'Michel',
            'lastName' => 'Michel',
            'email' => 'michel@michel.com',
            'plainPassword' => 'Test1passwordOk!',
            'name' => 'Nouvel atelier',
            'street' => 'rue de la justice',
            'streetNumber' => '8',
            'city' => 'Lille',
            'postcode' => '59000',
            'bikeTypesSupported' => ['/bike_types/'.$this->bikeTypes[0]->id, '/bike_types/'.$this->bikeTypes[1]->id],
            'repairerTypes' => ['/repairer_types/'.$this->repairerTypes[0]->id, '/repairer_types/'.$this->repairerTypes[1]->id],
            'comment' => 'Bonjour je voudrais rejoindre votre super plateforme',
        ];
    }

    public function testPostRepairerAndUserMissingFields(): void
    {
        $jsonMissingFields = $this->jsonNewRepairerAndUser;
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
            'json' => $this->jsonNewRepairerAndUser,
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
}
