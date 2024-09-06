<?php

namespace App\Service;
use Symfony\Component\HttpKernel\KernelInterface;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Psr\Log\LoggerInterface;

class ImageHandler
{
    private $kernel;
    private $logger;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    public function uploadImage($image, $productId)
    {
        $productImgDir = $this->kernel->getProjectDir(). '/public/images/product/';
        $imageName = $productId. '.png';

        if (!is_dir($productImgDir)) {
            mkdir($productImgDir, 0777, true);
        }

        // Check if the image already exists
        $imagePath = $productImgDir . $imageName;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $image->move($productImgDir, $imageName);
        
    }

    public function getImageLink($imgDir, $items)
    {
        $accessToken = getenv('DROPBOX_ACCESS_TOKEN');
        $dropboxApp = new DropboxApp("t03ew4kslhdea50", "lzizv35rwznpive", $accessToken);
        $dropbox = new Dropbox($dropboxApp);
    
        $links = [];
        $folderContents = $dropbox->listFolder('/images/' . $imgDir . '/');

        $this->logger->info('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
        $this->logger->info('BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB');
        echo "CCCCCCCCCCCCCCCCCCCCCCC";
    
        foreach ($items as $item) {
            $fileName = $item->getId() . '.png';
            foreach ($folderContents->getItems() as $file) {
                $name = $file->getName();
                $this->logger->info('Le fichier' . $name . 'est présent sur Dropbox');
                $this->logger->info('Le fichier' . $name . 'est présent sur Dropbox');
                if ($file instanceof DropboxFile && $file->getName() === $fileName) {
                    $path = '/images/' . $imgDir . '/' . $fileName;
                    $link = $dropbox->getTemporaryLink($path);
                    $links[$item->getId()] = $link;
                    break;
                }
            }
        }
    
        return $links;
    }
}