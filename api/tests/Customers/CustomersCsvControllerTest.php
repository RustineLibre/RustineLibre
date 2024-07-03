<?php

declare(strict_types=1);

namespace App\Tests\Customers;

use App\Entity\Repairer;
use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;

class CustomersCsvControllerTest extends AbstractTestCase
{
    private RepairerRepository $repairerRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repairerRepository = self::getContainer()->get(RepairerRepository::class);
    }

    public function testExportCsv(): void
    {
        /** @var Repairer $repairer */
        $repairer = $this->repairerRepository->findOneBy([]);

        $response = $this->createClientWithUser($repairer->owner)->request('GET', sprintf('/export_customers_csv/%d', $repairer->id));
        self::assertResponseIsSuccessful();

        $this->assertArrayHasKey('content-type', $response->getHeaders());
        $this->assertStringContainsString('text/csv', $response->getHeaders()['content-type'][0]);
        $this->assertStringContainsString('Id,Prenom,Nom,Email,Adresse,Ville', $response->getContent());
    }
}
