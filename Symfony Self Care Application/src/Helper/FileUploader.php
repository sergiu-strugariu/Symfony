<?php

namespace App\Helper;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    /** @var SluggerInterface */
    private SluggerInterface $slugger;

    /** @var KernelInterface */
    private KernelInterface $kernel;

    /** @var S3Client */
    private S3Client $s3Client;

    /** @var string */
    private string $bucket;

    /** @var DefaultHelper */
    protected DefaultHelper $helper;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $fileUploadRemoveLogger;

    /**
     * @param SluggerInterface $slugger
     * @param KernelInterface $kernel
     * @param DefaultHelper $helper
     * @param LoggerInterface $fileUploadRemoveLogger
     * @param string $accessKey
     * @param string $secretKey
     * @param string $region
     * @param string $endpoint
     * @param string $bucket
     */
    public function __construct(SluggerInterface $slugger, KernelInterface $kernel, DefaultHelper $helper, LoggerInterface $fileUploadRemoveLogger, string $accessKey, string $secretKey, string $region, string $endpoint, string $bucket)
    {
        $this->slugger = $slugger;
        $this->kernel = $kernel;
        $this->bucket = $bucket;
        $this->helper = $helper;
        $this->fileUploadRemoveLogger = $fileUploadRemoveLogger;

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ],
            'endpoint' => $endpoint,
        ]);
    }

    /**
     * @param UploadedFile $file
     * @param string $directory
     * @param bool $renameFile
     * @return string
     */
    public function upload(UploadedFile $file, string $directory, bool $renameFile = true): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $renameFile ? $safeFilename . '-' . uniqid() . '.' . $file->guessExtension() : $safeFilename . '.' . $file->guessExtension();

        $key = $directory . $fileName;

        try {
            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'SourceFile' => $file->getPathname(),
                'ACL' => 'public-read'
            ]);
        } catch (S3Exception $e) {
            throw new FileException('Failed to upload file to R2: ' . $e->getMessage());
        }

        return $fileName;
    }

    /**
     * @param object|null $file
     * @param $form
     * @param string $folderPath
     * @param string $field
     * @param bool $renameFile
     * @return array|null
     */
    public function uploadFile(object $file = null, $form, string $folderPath, string $field = 'fileName', bool $renameFile = true): ?array
    {
        $fileName = null;
        $success = true;

        if ($file instanceof UploadedFile) {
            try {
                $fileName = $this->upload($file, $folderPath, $renameFile);
            } catch (FileException $e) {
                $this->fileUploadRemoveLogger->error($e->getMessage(), ['fileName' => $fileName]);
                $success = false;
            }
        } else {
            $success = false;
        }

        if (!$success && $form !== null) {
            $form->get($field)->addError(new FormError(sprintf('The %s field is mandatory.', $field)));
        }

        return [
            'success' => $success,
            'fileName' => $fileName
        ];
    }


    /**
     * @param string $folderPath
     * @param string|null $fileName
     * @return bool
     */
    public function removeFile(string $folderPath, string|null $fileName): bool
    {
        $status = true;

        if (!empty($fileName) && $fileName !== $this->helper->getEnvValue('app_default_image')) {
            try {
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key' => $folderPath . $fileName,
                ]);
            } catch (S3Exception $e) {
                $this->fileUploadRemoveLogger->error($e->getMessage(), ['fileName' => $fileName]);
                $status = false;
            }
        }

        return $status;
    }
}
