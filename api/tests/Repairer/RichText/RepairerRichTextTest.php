<?php

declare(strict_types=1);

namespace App\Tests\Repairer\RichText;

use App\Entity\Repairer;
use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RepairerRichTextTest extends AbstractTestCase
{
    private const PAYLOAD = '<p onclick="alert(1)">Bonjour</p><img src=x onerror=alert(1)><script>alert(1)</script><a href="javascript:alert(1)">lien</a>';

    private Repairer $repairer;

    public function setUp(): void
    {
        parent::setUp();

        $this->repairer = static::getContainer()->get(RepairerRepository::class)->findOneBy(['enabled' => true]);
    }

    private function assertInert(?string $value): void
    {
        self::assertNotNull($value);
        self::assertStringContainsString('Bonjour', $value);
        self::assertDoesNotMatchRegularExpression('/<\s*(script|img)|\son\w+\s*=|javascript:/i', $value);
    }

    public function testPutSanitizesRichTextFields(): void
    {
        $response = self::createClientWithUser($this->repairer->owner)->request('PUT', sprintf('/repairers/%d', $this->repairer->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['description' => self::PAYLOAD, 'openingHours' => self::PAYLOAD, 'optionalPage' => self::PAYLOAD],
        ]);

        self::assertResponseIsSuccessful();
        $json = $response->toArray();
        foreach (['description', 'openingHours', 'optionalPage'] as $field) {
            $this->assertInert($json[$field]);
        }

        $stored = static::getContainer()->get(EntityManagerInterface::class)->getConnection()
            ->fetchAssociative('SELECT description, opening_hours, optional_page FROM repairer WHERE id = ?', [$this->repairer->id]);
        foreach ($stored as $value) {
            $this->assertInert($value);
        }
    }

    public function testPatchSanitizesRichTextFields(): void
    {
        $response = self::createClientWithUser($this->repairer->owner)->request('PATCH', sprintf('/repairers/%d', $this->repairer->id), [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => ['description' => self::PAYLOAD],
        ]);

        self::assertResponseIsSuccessful();
        $this->assertInert($response->toArray()['description']);
    }

    public function testCommandSanitizesExistingRecords(): void
    {
        $connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $connection->executeStatement('UPDATE repairer SET description = ?, optional_page = ? WHERE id = ?', [self::PAYLOAD, self::PAYLOAD, $this->repairer->id]);

        static::getContainer()->get(EntityManagerInterface::class)->clear();

        $application = new Application(static::$kernel);
        $tester = new CommandTester($application->find('app:repairer:sanitize-rich-text'));
        $tester->execute([]);
        $tester->assertCommandIsSuccessful();

        $stored = $connection->fetchAssociative('SELECT description, optional_page FROM repairer WHERE id = ?', [$this->repairer->id]);
        foreach ($stored as $value) {
            $this->assertInert($value);
        }
    }
}
