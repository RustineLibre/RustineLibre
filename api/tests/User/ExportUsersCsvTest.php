<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExportUsersCsvTest extends AbstractTestCase
{
    private const ENDPOINT = '/export_users_csv';

    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = self::getContainer()->get(UserRepository::class);
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
        $expectedHeader = ['Nom', 'Prénom', 'Email', 'Tel', 'Dernière_Connexion'];
        $this->assertEquals($expectedHeader, $header);

        $rowsCount = count($rows) - 2; // -1 for header, -1 for last empty line from csv file
        $usersCount = count($this->userRepository->findAll());
        $this->assertEquals($usersCount, $rowsCount);
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
        self::assertResponseStatusCodeSame(403);

        $this->createClientAuthAsRepairer(['Accept' => 'text/csv'])->request('GET', self::ENDPOINT);
        self::assertResponseStatusCodeSame(403);

        $this->createClientWithCredentials([], ['Accept' => 'text/csv'])->request('GET', self::ENDPOINT);
        self::assertResponseStatusCodeSame(403);
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
        self::assertResponseStatusCodeSame(401);
    }
}
