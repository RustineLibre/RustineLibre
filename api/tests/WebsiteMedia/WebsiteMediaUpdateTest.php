<?php

declare(strict_types=1);

namespace App\Tests\WebsiteMedia;

use App\Entity\MediaObject;
use App\Repository\MediaObjectRepository;
use App\Repository\WebsiteMediaRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class WebsiteMediaUpdateTest extends AbstractTestCase
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

    public function testUpdateByAdmin(): void
    {
        $client = $this->createClientAuthAsAdmin();
        // Create a mediaObject for the test with an admin role
        $file = new UploadedFile(sprintf('%s/../../fixtures/%s', __DIR__, self::IMAGE_NAME), self::IMAGE_NAME);
        $mediaResponse = $client->request('POST', '/media_objects/images', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $file,
                ],
            ],
        ]);
        self::assertResponseIsSuccessful();
        $mediaResponseId = preg_match('/\/(\d+)(\/|$)/', $mediaResponse->toArray()['@id'], $match);

        //create a website media with the media object and a predefined test id with an admin role
        $client->request('POST', '/website_media', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'id' => self::WEBSITE_MEDIA_ID,
                'media' => $mediaResponse->toArray()['@id'],
            ]
        ]);

        self::assertResponseIsSuccessful();

        // Check if created website media exist
        $websiteMediaCreated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);
        self::assertNotEmpty($websiteMediaCreated);

        $newMedia = $this->mediaObjectRepository->findAll();

        $newMedia = $newMedia[0]->id !== $mediaResponseId ? $newMedia[0] : $newMedia[1];

        $client->request('PATCH', '/website_media/' . $websiteMediaCreated->id, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'media' => sprintf('/media_objects/%s', $newMedia->id),
            ]
        ]);

        self::assertResponseIsSuccessful();

        // Check if created website media exist
        $websiteMediaUpdated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);
        self::assertNotSame($websiteMediaUpdated->media->id, $mediaResponseId);

    }

    public function testUpdateByBoss(): void
    {
        $websiteMediaCreated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);
        self::assertNotEmpty($websiteMediaCreated);

        $mediaObject = $this->mediaObjectRepository->findOneBy(['id' => $websiteMediaCreated->media->id]);

        self::assertNotEmpty($mediaObject);

        $newMedia = $this->mediaObjectRepository->findAll();

        $newMedia = $newMedia[0]->id !== $mediaObject->id ? $newMedia[0] : $newMedia[1];

        $this->createClientAuthAsBoss()->request('PATCH', '/website_media/' . $websiteMediaCreated->id, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'media' => sprintf('/media_objects/%s', $newMedia->id),
            ]
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

    }

    public function testUpdateByUser(): void
    {
        $websiteMediaCreated = $this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]);
        self::assertNotEmpty($websiteMediaCreated);

        $mediaObject = $this->mediaObjectRepository->findOneBy(['id' => $websiteMediaCreated->media->id]);

        self::assertNotEmpty($mediaObject);

        $newMedia = $this->mediaObjectRepository->findAll();

        $newMedia = $newMedia[0]->id !== $mediaObject->id ? $newMedia[0] : $newMedia[1];

        $this->createClientAuthAsUser()->request('PATCH', '/website_media/' . $websiteMediaCreated->id, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'media' => sprintf('/media_objects/%s', $newMedia->id),
            ]
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //delete objects created by tests
        $this->createClientAuthAsAdmin()->request('DELETE', sprintf('/website_media/%s', $websiteMediaCreated->id));
        // Check if both objects are well deleted
        self::assertEmpty($this->websiteMediaRepository->findOneBy(['id' => self::WEBSITE_MEDIA_ID]));
        self::assertEmpty($this->mediaObjectRepository->findOneBy(['id' => $mediaObject->id]));
    }
}