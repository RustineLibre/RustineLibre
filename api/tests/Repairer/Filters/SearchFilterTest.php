<?php

declare(strict_types=1);

namespace App\Tests\Repairer\Filters;

use App\Repository\RepairerRepository;
use App\Repository\RepairerTypeRepository;
use App\Tests\AbstractTestCase;

class SearchFilterTest extends AbstractTestCase
{
    private array $repairerTypes = [];

    private RepairerRepository $repairerRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repairerTypes = static::getContainer()->get(RepairerTypeRepository::class)->findAll();
        $this->repairerRepository = static::getContainer()->get(RepairerRepository::class);
    }

    public function testSearchFilter(): void
    {
        $response = static::createClient()->request('GET', '/repairers?repairerTypes.name=atelier');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseData = $response->toArray();
        $this->assertEquals(16, $responseData['hydra:totalItems']);

        // test if first and second repairers have the "atelier" repairer bike type
        $firstRepairer = static::createClient()->request('GET', $responseData['hydra:member'][0]['@id'])->toArray();
        $secondRepairer = static::createClient()->request('GET', $responseData['hydra:member'][1]['@id'])->toArray();

        $this->assertStringContainsString('atelier', strtolower($firstRepairer['repairerTypes'][0]['name']));
        $this->assertStringContainsString('atelier', strtolower($secondRepairer['repairerTypes'][0]['name']));
    }

    public function testSearchFilterByRepairerTypesId(): void
    {
        $response = static::createClient()->request('GET', '/repairers?repairerTypes.id='.$this->repairerTypes[0]->id);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseData = $response->toArray();
        $this->assertEquals(16, $responseData['hydra:totalItems']);

        $response2 = static::createClient()->request('GET', '/repairers?repairerTypes.id='.$this->repairerTypes[1]->id);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseData2 = $response2->toArray();
        $this->assertEquals(7, $responseData2['hydra:totalItems']);

        // Get a repairer that don't have a repairerTypes[1]

        $newRepairer = $this->repairerRepository->findOneBy(['id' => $responseData['hydra:member'][0]['id']]);

        // Change from repairerTypes[0] to repairerTypes[1]

        $this->createClientWithUser($newRepairer->owner)->request('PATCH', '/repairers/'.$newRepairer->id, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
            'repairerTypes' => ['/repairer_types/'.$this->repairerTypes[1]->id],
        ]]);

        // Check if the first response have one element less
        $newResponse1 = static::createClient()->request('GET', '/repairers?repairerTypes.id='.$this->repairerTypes[0]->id);
        $newResponseData = $newResponse1->toArray();
        $this->assertEquals(15, $newResponseData['hydra:totalItems']);

        // Check if the second response have one element more
        $newResponse2 = static::createClient()->request('GET', '/repairers?repairerTypes.id='.$this->repairerTypes[1]->id);
        $newResponse2Data = $newResponse2->toArray();
        $this->assertEquals(8, $newResponse2Data['hydra:totalItems']);
    }

    public function testSearchFilterByRepairerTypesIdWithMultipleRepairerTypes(): void
    {
        $response = static::createClient()->request('GET', '/repairers?repairerTypes.id='.$this->repairerTypes[0]->id);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseData = $response->toArray();
        $this->assertEquals(15, $responseData['hydra:totalItems']);

        $response2 = static::createClient()->request('GET', '/repairers?repairerTypes.id='.$this->repairerTypes[1]->id);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseData2 = $response2->toArray();
        $this->assertEquals(8, $responseData2['hydra:totalItems']);

        // Get a repairer that don't have a repairerTypes[1]
        $newRepairer = $this->repairerRepository->findOneBy(['id' => $responseData['hydra:member'][0]['id']]);

        // Add a repairerTypes[1]
        $lastResponse = $this->createClientWithUser($newRepairer->owner)->request('PATCH', '/repairers/'.$newRepairer->id, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
            'repairerTypes' => ['/repairer_types/'.$this->repairerTypes[1]->id, '/repairer_types/'.$this->repairerTypes[0]->id],
        ]]);

        // Check if first response is equal
        $newResponse1 = static::createClient()->request('GET', '/repairers?repairerTypes.id='.$this->repairerTypes[0]->id);
        $newResponseData = $newResponse1->toArray();
        $this->assertEquals(15, $newResponseData['hydra:totalItems']);

        // check if second response have one repairerTypes more
        $newResponse2 = static::createClient()->request('GET', '/repairers?repairerTypes.id='.$this->repairerTypes[1]->id);
        $newResponse2Data = $newResponse2->toArray();

        $this->assertEquals(9, $newResponse2Data['hydra:totalItems']);
    }
}
