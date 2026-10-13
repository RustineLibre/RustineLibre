<?php

declare(strict_types=1);

namespace App\Tests\Medias;

use App\Entity\Bike;
use App\Entity\Maintenance;
use App\Entity\MediaObject;
use App\Entity\User;
use App\Repository\AutoDiagnosticRepository;
use App\Repository\BikeRepository;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * A media is only readable by its owner, and can only be attached to a resource by its owner:
 * deleting the resource deletes the media it references.
 */
class MediaObjectAuthorizationTest extends AbstractTestCase
{
    private User $victim;

    private User $attacker;

    /** @var list<array{class-string, int}> */
    private array $created = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->victim = $this->createUser('victim');
        $this->attacker = $this->createUser('attacker');
    }

    protected function tearDown(): void
    {
        $em = $this->em();
        $em->clear();
        foreach (array_reverse($this->created) as [$class, $id]) {
            if (null !== $entity = $em->find($class, $id)) {
                $em->remove($entity);
                $em->flush();
            }
        }
        parent::tearDown();
    }

    // Reading a media

    public function testAnonymousCannotGetAMedia(): void
    {
        $iri = $this->uploadImage($this->victim);

        $response = self::createClient()->request('GET', $iri);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertStringNotContainsString('contentUrl', $response->getContent(false));
    }

    public function testAnotherUserCannotGetAMedia(): void
    {
        $iri = $this->uploadImage($this->victim);

        $response = $this->createClientWithUser($this->attacker)->request('GET', $iri);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertStringNotContainsString('contentUrl', $response->getContent(false));
    }

    public function testOwnerAndAdminCanGetAMedia(): void
    {
        $iri = $this->uploadImage($this->victim);

        $response = $this->createClientWithUser($this->victim)->request('GET', $iri)->toArray();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('contentUrl', $response);

        $this->createClientAuthAsAdmin()->request('GET', $iri);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAnonymousCannotDeleteAMediaWithoutOwner(): void
    {
        $iri = $this->uploadImage($this->victim);
        $this->removeOwner($iri);

        self::createClient()->request('DELETE', $iri);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertNotNull($this->em()->find(MediaObject::class, $this->idFromIri($iri)));
    }

    public function testAnotherUserCannotDeleteAMediaWithoutOwner(): void
    {
        $iri = $this->uploadImage($this->victim);
        $this->removeOwner($iri);

        $this->createClientWithUser($this->attacker)->request('DELETE', $iri);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    // Attaching a media of someone else

    public function testCannotAttachAMediaOfSomeoneElseToANewBike(): void
    {
        $victimMedia = $this->uploadImage($this->victim);

        $this->createClientWithUser($this->attacker)->request('POST', '/bikes', ['json' => ['name' => 'Bike', 'picture' => $victimMedia]]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCannotAttachAMediaOfSomeoneElseToAnExistingBike(): void
    {
        $victimMedia = $this->uploadImage($this->victim);
        $bike = $this->createBike($this->attacker);

        foreach (['picture', 'wheelPicture', 'transmissionPicture'] as $property) {
            $this->createClientWithUser($this->attacker)->request('PUT', $bike, ['json' => [$property => $victimMedia]]);
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, $property);
        }
    }

    public function testDeletingABikeNeverDeletesTheMediaOfSomeoneElse(): void
    {
        $victimMedia = $this->uploadImage($this->victim);
        $bike = $this->createBike($this->attacker);
        $this->createClientWithUser($this->attacker)->request('PUT', $bike, ['json' => ['picture' => $victimMedia]]);

        $this->createClientWithUser($this->attacker)->request('DELETE', $bike);

        self::assertNotNull($this->em()->find(MediaObject::class, $this->idFromIri($victimMedia)));
    }

    public function testCannotAttachAMediaOfSomeoneElseToAMaintenance(): void
    {
        $victimMedia = $this->uploadImage($this->victim);
        $bike = $this->createBike($this->attacker);

        foreach (['photo', 'invoice'] as $property) {
            $this->createClientWithUser($this->attacker)->request('POST', '/maintenances', ['json' => ['name' => 'Maintenance', 'bike' => $bike, $property => $victimMedia]]);
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, $property);
        }
    }

    public function testCannotAttachAMediaOfSomeoneElseToAnAutoDiagnostic(): void
    {
        $victimMedia = $this->uploadImage($this->victim);
        $autoDiagnostic = self::getContainer()->get(AutoDiagnosticRepository::class)->findOneBy([]);

        $this->createClientWithUser($autoDiagnostic->appointment->customer)->request('PUT', sprintf('/auto_diagnostics/%d', $autoDiagnostic->id), ['json' => ['photo' => $victimMedia]]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCannotAttachAMediaOfSomeoneElseToARepairer(): void
    {
        $victimMedia = $this->uploadImage($this->victim);
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy(['name' => 'Chez Johnny']);

        foreach (['thumbnail', 'descriptionPicture'] as $property) {
            $this->createClientWithUser($repairer->owner)->request('PUT', sprintf('/repairers/%d', $repairer->id), ['json' => [$property => $victimMedia]]);
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, $property);
        }
    }

    // What must keep working

    public function testOwnerCanAttachHisMedia(): void
    {
        $media = $this->uploadImage($this->attacker);

        $this->createClientWithUser($this->attacker)->request('POST', '/bikes', ['json' => ['name' => 'Bike', 'picture' => $media]]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->track(Bike::class, $this->idFromIri($this->createClientAuthAsAdmin()->request('GET', '/bikes?name=Bike&owner='.$this->attacker->id)->toArray()['hydra:member'][0]['@id']));
    }

    public function testAdminCanAttachAnyMedia(): void
    {
        $media = $this->uploadImage($this->victim);
        $bike = $this->createBike($this->attacker);

        $this->createClientAuthAsAdmin()->request('PUT', $bike, ['json' => ['picture' => $media]]);

        self::assertResponseIsSuccessful();
    }

    public function testStaffCanUpdateAMaintenanceWithoutChangingItsMedia(): void
    {
        $customer = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user1@test.com']);
        $boss = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'boss@test.com']);
        $bike = $this->iri('/bikes', self::getContainer()->get(BikeRepository::class)->findOneBy(['owner' => $customer])->id);
        $photo = $this->uploadImage($customer);
        $maintenance = $this->createClientWithUser($customer)->request('POST', '/maintenances', ['json' => ['name' => 'Maintenance', 'bike' => $bike, 'photo' => $photo]])->toArray()['@id'];
        $this->track(Maintenance::class, $this->idFromIri($maintenance));

        // The boss is not the owner of the photo, but does not change it
        $this->createClientWithUser($boss)->request('PUT', $maintenance, ['json' => ['name' => 'Renamed', 'bike' => $bike, 'photo' => $photo]]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }

    private function createUser(string $name): User
    {
        $user = new User();
        $user->email = sprintf('%s-%s@media-authorization.test', $name, bin2hex(random_bytes(4)));
        $user->firstName = ucfirst($name);
        $user->lastName = 'Media';
        $user->plainPassword = 'Test1passwordOk!';
        $user->roles = ['ROLE_USER'];
        $user->emailConfirmed = true;
        $this->em()->persist($user);
        $this->em()->flush();
        $this->track(User::class, $user->id);

        return $user;
    }

    private function uploadImage(User $user): string
    {
        $file = new UploadedFile(sprintf('%s/../../fixtures/%s', __DIR__, MediasTest::IMAGE_NAME), MediasTest::IMAGE_NAME);
        $iri = $this->createClientWithUser($user)->request('POST', '/media_objects/images', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => ['files' => ['file' => $file]],
        ])->toArray()['@id'];
        $this->track(MediaObject::class, $this->idFromIri($iri));

        return $iri;
    }

    private function createBike(User $user): string
    {
        $iri = $this->createClientWithUser($user)->request('POST', '/bikes', ['json' => ['name' => 'Bike']])->toArray()['@id'];
        $this->track(Bike::class, $this->idFromIri($iri));

        return $iri;
    }

    private function removeOwner(string $mediaIri): void
    {
        $media = $this->em()->find(MediaObject::class, $this->idFromIri($mediaIri));
        $media->owner = null;
        $this->em()->flush();
        $this->em()->clear();
    }

    private function iri(string $collection, int $id): string
    {
        return sprintf('%s/%d', $collection, $id);
    }

    private function idFromIri(string $iri): int
    {
        return (int) basename($iri);
    }

    private function track(string $class, int $id): void
    {
        $this->created[] = [$class, $id];
    }
}
