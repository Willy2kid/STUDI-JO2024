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
    private $appKey;
    private $appSecret;

    public function __construct(KernelInterface $kernel, DropboxService $dropboxService, ParameterBagInterface $parameterBag)
    {
        $this->kernel = $kernel;
        $this->dropboxService = $dropboxService;
        // $this->appKey = $parameterBag->get('DROPBOX_APP_KEY');
        // $this->appSecret = $parameterBag->get('DROPBOX_APP_SECRET');
        $this->appSecret = $parameterBag->get('env(DROPBOX_APP_SECRET)');
        $this->appKey = $parameterBag->get('env(DROPBOX_KEY)');
        dump($this->appKey, $this->appSecret);
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
        $appSecret = getenv('DROPBOX_APP_SECRET');
        $appKey = getenv('DROPBOX_APP_KEY');
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