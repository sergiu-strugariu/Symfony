<?php

namespace App\Helper;

use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    /**
     * @var SluggerInterface
     */
    private SluggerInterface $slugger;

    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @param SluggerInterface $slugger
     * @param KernelInterface $kernel
     * @param Filesystem $filesystem
     */
    public function __construct(SluggerInterface $slugger, KernelInterface $kernel, Filesystem $filesystem)
    {
        $this->slugger = $slugger;
        $this->kernel = $kernel;
        $this->filesystem = $filesystem;
    }

    /**
     * @param UploadedFile $file
     * @param $directory
     * @param bool $renameFile
     * @return string
     */
    public function upload(UploadedFile $file, $directory, bool $renameFile = true): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $renameFile ? $safeFilename . '-' . uniqid() . '.' . $file->guessExtension() : $safeFilename . '.' . $file->guessExtension();

        try {
            $file->move($directory, $fileName);
        } catch (FileException $e) {
            throw $e;
        }

        return $fileName;
    }

    /**
     * @param object|null $file
     * @param $form
     * @param string $folderPath
     * @param string $field
     * @param string $root
     * @param bool $renameFile
     * @return array|null
     */
    public function uploadFile(object $file = null, $form, string $folderPath, string $field = 'image', string $root = 'public', bool $renameFile = true): ?array
    {
        $fileName = null;
        $success = true;

        if ($file instanceof UploadedFile) {
            // Upload file
            try {
                $fileName = $this->upload($file, sprintf('%s/%s/%s', $this->kernel->getProjectDir(), $root, $folderPath), $renameFile);
            } catch (FileException $e) {
                $success = false;
            }
        } else {
            $success = false;
        }

        // Set error form
        if (!$success && !empty($form)) {
            $form->get($field)->addError(new FormError(sprintf('The %s field is mandatory.', $field)));
        }

        return [
            'success' => $success,
            'fileName' => $fileName
        ];
    }


    /**
     * Removes a file by its path.
     *
     * @param string $folderPath
     * @param string $fileName
     * @return bool
     * @throws Exception
     */
    public function removeFile(string $folderPath, string $fileName): bool
    {
        $filePath = sprintf('%s/%s/%s/%s', $this->kernel->getProjectDir(), 'public', $folderPath, $fileName);

        try {
            $this->filesystem->remove($filePath);
            return true;
        } catch (FileException $e) {
            throw new Exception("Failed to delete file: " . $filePath . ". Error: " . $e->getMessage());
        }
    }
}
