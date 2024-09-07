<?php

namespace App\Service;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use GuzzleHttp\Client;
use App\Service\DropboxService;

class ImageHandler
{
    private $kernel;
    private $dropboxService;

    public function __construct(KernelInterface $kernel, DropboxService $dropboxService)
    {
        $this->kernel = $kernel;
        $this->dropboxService = $dropboxService;
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

    public function getImageLink($imgDir, $items, string $accessToken, ParameterBagInterface $parameterBag)
    {
        $appKey = $parameterBag->get('DROPBOX_APP_KEY');
        $appSecret = $parameterBag->get('DROPBOX_APP_SECRET');
        $redirectUri = $parameterBag->get('DROPBOX_REDIRECT_URI');

        $dropboxApp = new DropboxApp($appKey, $appSecret, $accessToken);
        $dropbox = new Dropbox($dropboxApp);

        $links = [];
        $itemIds = [];
        foreach ($items as $item) { $itemIds[] = $item->getId(); }
        $folderPath = '/images/' . $imgDir .'/';
        $files = $dropbox->listFolder($folderPath)->getItems();

        foreach ($files as $file) {
            $fileName = $file->getName();
            $fileNameNoExt = str_replace('.png', '', $fileName);
            if (in_array($fileNameNoExt, $itemIds)) {
                $link = $dropbox->getTemporaryLink($folderPath . $fileName)->getLink();
                $links[$fileNameNoExt] = $link;
            }
        }

        return $links;
    }
}