<?php

declare(strict_types=1);

namespace App\Tests\Medias;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\MediaObject;
use App\Flysystem\MediaObjectManager;
use App\Repository\MediaObjectRepository;
use App\Tests\AbstractTestCase;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Each upload is stored under its own key: two media objects never share a stored file.
 */
class MediaStorageKeyTest extends AbstractTestCase
{
    private const IMAGE_NAME = 'ratpi.png';

    public function testUploadsOfTheSameFileNameAreStoredSeparately(): void
    {
        $first = $this->upload($this->createClientAuthAsUser());
        $second = $this->upload($this->createClientAuthAsBoss());

        self::assertNotSame($first->filePath, $second->filePath);
        self::assertStringEndsWith('-ratpi-png', (string) $first->filePath);
    }

    public function testDeletingAMediaKeepsTheFileOfAnotherUpload(): void
    {
        $first = $this->upload($this->createClientAuthAsUser());
        $second = $this->upload($this->createClientAuthAsBoss());

        $this->createClientAuthAsUser()->request('DELETE', sprintf('/media_objects/%d', $first->id));
        self::assertResponseStatusCodeSame(204);

        self::assertTrue($this->imagesStorage()->fileExists((string) $second->filePath));
    }

    public function testAStoredFileIsNeverOverwritten(): void
    {
        $existing = $this->upload($this->createClientAuthAsUser());

        $mediaObject = new MediaObject();
        $mediaObject->file = new UploadedFile($this->copyOfFixtureImage(), self::IMAGE_NAME);
        $mediaObject->filePath = $existing->filePath;

        /** @var MediaObjectManager $manager */
        $manager = self::getContainer()->get(MediaObjectManager::class);
        $this->expectException(\RuntimeException::class);
        $manager->upload($mediaObject, 'images');
    }

    private function upload(Client $client): MediaObject
    {
        $response = $client->request('POST', '/media_objects/images', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => ['files' => ['file' => new UploadedFile($this->copyOfFixtureImage(), self::IMAGE_NAME)]],
        ]);
        self::assertResponseIsSuccessful();

        /** @var MediaObjectRepository $repository */
        $repository = self::getContainer()->get(MediaObjectRepository::class);
        /** @var MediaObject $mediaObject */
        $mediaObject = $repository->find(basename($response->toArray()['@id']));

        return $mediaObject;
    }

    private function copyOfFixtureImage(): string
    {
        $path = sprintf('%s/%s-%s', sys_get_temp_dir(), bin2hex(random_bytes(4)), self::IMAGE_NAME);
        copy(sprintf('%s/../../fixtures/%s', __DIR__, self::IMAGE_NAME), $path);

        return $path;
    }

    private function imagesStorage(): FilesystemOperator
    {
        /** @var FilesystemOperator $storage */
        $storage = self::getContainer()->get('images.storage');

        return $storage;
    }
}
