<?php

namespace App\Service;
use Symfony\Component\HttpKernel\KernelInterface;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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

    // public function getImageLink($imgDir, $items)
    // {
    //     $accessToken = getenv('DROPBOX_ACCESS_TOKEN');
    //     $dropboxApp = new DropboxApp("t03ew4kslhdea50", "lzizv35rwznpive", $accessToken);
    //     $dropbox = new Dropbox($dropboxApp);
    // 
    //     $links = [];
    // 
    //     foreach ($items as $item) {
    //         $fileName = $item->getId() . '.png';
    //         $filePath = '/images/' . $imgDir . '/' . $fileName;
    //         $folderPath = '/images/' . $imgDir;
    //         $searchResult = $dropbox->search($folderPath, $fileName);
    //         $path = '/images/product/1.png';
    //
    //         if ($searchResult) {
    //             $link = $dropbox->getTemporaryLink($path);
    //             $links[$item->getId()] = $link->url;
    //             // $links[] = $dropbox->getTemporaryLink($searchResult->getItems()->first()->getPath());
    //             // echo 'Lien généré pour ' . $name . ' sur dropbox et doit être égale à ' . $fileName;
    //         }
    //     }
    //     $this->logger->info('tableau des liens: ' . count($links));
    //     return $links;
    // }

    public function getImageLink($imgDir, $items)
    {
        $accessToken = getenv('DROPBOX_ACCESS_TOKEN');
        $dropboxApp = new DropboxApp("t03ew4kslhdea50", "lzizv35rwznpive", $accessToken);
        $dropbox = new Dropbox($dropboxApp);

        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stderr', LogLevel::INFO));

        $links = [];
        $folderPath = '/images/' . $imgDir .'/';
        $files = $dropbox->listFolder($folderPath);

        if (empty($files)) {
            $this->logger->log(LogLevel::INFO, "No files found in folder $folderPath");
        } else {
            $this->logger->log(LogLevel::INFO, "fichiers présent sur dropbox");
        }

        foreach ($files as $file) {
            $fileName = '1.png';
            $this->logger->log(LogLevel::INFO, 'le nom du ficher dropbox est ' . $file->getName());
            if ($file->getName() === $fileName)
            {
                $fileMetadata = $dropbox->getFileMetadata($file->getPath());
                $link = $dropbox->createTemporaryDirectLink($fileMetadata->getPath())->getLink();
                $links[$file->getName()] = $link;
            }
        }

        $this->logger->log(LogLevel::INFO, 'tableau des liens: ' . count($links));

        return $links;

        // // Batch the API calls to reduce the number of calls made
        // $batch = [];
        // foreach ($items as $item) {
        //     $fileName = $item->getId() . '.png';
        //     $batch[] = $fileName;
        // 
        // // Make a single API call to search for all files in the batch
        // $searchResult = $dropbox->search($folderPath, implode(',', $batch))     // 
        // // Loop through the search results and get the temporary link for each file
        // foreach ($searchResult->getItems() as $file) {
        //     $link = $dropbox->getTemporaryLink($file->getPath())->getLink();
        //     $links[$file->getName()] = $link->url;
        // }

    }
}