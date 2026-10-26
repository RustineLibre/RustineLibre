<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MediaObject;
use App\Flysystem\MediaObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateMediaObjectAction extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly MediaObjectManager $mediaObjectManager,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function __invoke(Request $request): MediaObject
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException($this->translator->trans('400_badRequest.file.required', domain: 'validators'));
        }

        $mediaObject = new MediaObject();
        $mediaObject->file = $uploadedFile;
        $mediaObject->owner = $this->security->getUser();
        $mediaObject = $this->addFilePath($mediaObject);
        $prefix = 'media_object_add_image' === $request->get('_route') ? 'images' : 'files';
        $this->mediaObjectManager->upload($mediaObject, $prefix);

        return $mediaObject;
    }

    public function addFilePath(MediaObject $mediaObject): MediaObject
    {
        $slugger = new AsciiSlugger();
        // The storage key must be unique: two uploads must never share the same stored object.
        $randomString = bin2hex(random_bytes(16));

        if (!$mediaObject->file instanceof UploadedFile) {
            $mediaObject->filePath = $slugger->slug(sprintf('%d-%s.%s', time(), $randomString, $mediaObject->file->guessExtension()))->toString();
        } else {
            $parts = explode('.', $mediaObject->file->getClientOriginalName());
            $slugName = (string) $this->slugger->slug(strtolower($parts[0]));
            $mediaObject->filePath = $slugger->slug(sprintf('%d-%s-%s.%s', time(), $randomString, $slugName, $mediaObject->file->getClientOriginalExtension()))->toString();
        }

        return $mediaObject;
    }
}
