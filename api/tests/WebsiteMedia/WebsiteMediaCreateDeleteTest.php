<?php

declare(strict_types=1);

namespace App\Tests\WebsiteMedia;

use App\Repository\MediaObjectRepository;
use App\Repository\WebsiteMediaRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class WebsiteMediaCreateDeleteTest extends AbstractTestCase
{
    public const IMAGE_NAME = 'ratpi.png';

    public const WEBSITE_MEDIA_ID = 'test_website_media';

    private MediaObjectRepository $mediaObjectRepository;

    private WebsiteMediaRepository $websiteMediaRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mediaObjectRepository = self::getContainer()->get(MediaObjectRepository::class);
        $this->websiteMediaRepository = self::getContainer()->get(WebsiteMediaRepository::class);
    }

    public function testCreateWebsiteMediaByAdmin(): void
    {
        // Create a mediaObject for the test with an admin role
        $file = new UploadedFile(sprintf('%s/../../fixtures/%s', __DIR__, self::IMAGE_NAME), self::IMAGE_NAME);
        $mediaResponse = $this->createClientAuthAsAdmin()->request('POST', '/media_objects/images', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $file,
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();

        // create a website media with the media object and a predefined test id with an admin role
        $this->createClientAuthAsAdmin()->request('POST', '/website_media', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'id' => self::WEBSITE_MEDIA_ID,
                'media' => $mediaResponse->toArray()['@id'],
            ],
        ]);

        self::assertResponseIsSuccessful();

        // Check if created website media exist
        $websiteMediaCreated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);
        self::assertNotNull($websiteMediaCreated);
    }

    public function testDeleteWebsiteMediaByAdmin(): void
    {
        // Retrieve the website Media created in the test before
        $websiteMediaCreated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);

        $mediaObjectId = $websiteMediaCreated->media->id;

        // Delete the website media with an admin account
        $this->createClientAuthAsAdmin()->request('DELETE', sprintf('/website_media/%s', $websiteMediaCreated->id));

        self::assertResponseIsSuccessful();

        // Check if both objects are well deleted
        self::assertNull($this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]));
        self::assertNull($this->mediaObjectRepository->findOneBy(['id' => $mediaObjectId]));
    }

    public function testCreateWebsiteMediaByBoss(): void
    {
        // Create a mediaObject for the test with an admin role
        $file = new UploadedFile(sprintf('%s/../../fixtures/%s', __DIR__, self::IMAGE_NAME), self::IMAGE_NAME);
        $mediaResponse = $this->createClientAuthAsAdmin()->request('POST', '/media_objects/images', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $file,
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();

        // create a website media with the media object and a predefined test id with a boss role
        $this->createClientAuthAsBoss()->request('POST', '/website_media', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'id' => self::WEBSITE_MEDIA_ID,
                'media' => $mediaResponse->toArray()['@id'],
            ],
        ]);

        // check if boss cannot create a websiteMedia Object
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // create a website media with the media object with admin role for the next tests
        $this->createClientAuthAsAdmin()->request('POST', '/website_media', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'id' => self::WEBSITE_MEDIA_ID,
                'media' => $mediaResponse->toArray()['@id'],
            ],
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testDeleteWebsiteMediaByBoss(): void
    {
        // Retrieve the website Media and media Object created in the test before
        $websiteMediaCreated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);

        $mediaObjectId = $websiteMediaCreated->media->id;

        // Delete the website media with an admin account
        $this->createClientAuthAsBoss()->request('DELETE', sprintf('/website_media/%s', $websiteMediaCreated->id));

        // check if boss cannot delete a websiteMedia Object
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateWebsiteMediaByUser(): void
    {
        // Retrieve the website Media created in the test before
        $websiteMediaCreated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);

        $mediaObject = $websiteMediaCreated->media;

        // create a website media with the media object and a predefined test id with a repairer role
        $this->createClientAuthAsUser()->request('POST', '/website_media', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'id' => self::WEBSITE_MEDIA_ID,
                'media' => $mediaObject,
            ],
        ]);

        // check if user cannot create a websiteMedia Object
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteWebsiteMediaByUser(): void
    {
        // Retrieve the website Media created in the test before
        $websiteMediaCreated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);

        $mediaObjectId = $websiteMediaCreated->media->id;

        // Delete the website media with an user account
        $this->createClientAuthAsUser()->request('DELETE', sprintf('/website_media/%s', $websiteMediaCreated->id));

        // check if repairer cannot delete a websiteMedia Object
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // delete objects created by tests
        $this->createClientAuthAsAdmin()->request('DELETE', sprintf('/website_media/%s', $websiteMediaCreated->id));
        // Check if both objects are well deleted
        self::assertNull($this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]));
        self::assertNull($this->mediaObjectRepository->findOneBy(['id' => $mediaObjectId]));
    }
}
