<?php

declare(strict_types=1);

namespace App\Tests\Repairer;

use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExportRepairersCsvTest extends AbstractTestCase
{
    private const ENDPOINT = '/export_repairers_csv';

    private RepairerRepository $repairerRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repairerRepository = self::getContainer()->get(RepairerRepository::class);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testExportCsvAsRoleAdmin(): void
    {
        $response = $this->createClientAuthAsAdmin(['Accept' => 'text/csv'])->request('GET', self::ENDPOINT);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/csv; charset=utf-8');

        $rows = explode(PHP_EOL, $response->getContent());

        $header = str_getcsv($rows[0]);
        $expectedHeader = ['Nom', 'Prénom', 'Email', 'Tel', 'Tel_Réparateur', 'Types', 'Nom_Réparateur', 'Date_Création', 'Dernière_Connexion'];
        $this->assertEquals($expectedHeader, $header);

        $rowsCount = count($rows) - 2; // -1 for header, -1 for last empty line from csv file
        $repairersCount = count($this->repairerRepository->findAll());
        $this->assertEquals($repairersCount, $rowsCount);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetCollectionForbidden(): void
    {
        $this->createClientAuthAsBoss(['Accept' => 'text/csv'])->request('GET', self::ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientAuthAsUser(['Accept' => 'text/csv'])->request('GET', self::ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientAuthAsRepairer(['Accept' => 'text/csv'])->request('GET', self::ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->createClientWithCredentials([], ['Accept' => 'text/csv'])->request('GET', self::ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetCollectionUnauthorized(): void
    {
        static::createClient([], ['headers' => ['Accept' => 'text/csv']])->request('GET', self::ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
