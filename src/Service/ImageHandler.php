<?php

namespace App\Service;
use Symfony\Component\HttpKernel\KernelInterface;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

class ImageHandler
{
    private $kernel;

    public function __construct(KernelInterface $kernel,)
    {
        $this->kernel = $kernel;
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
        foreach ($items as $item) {
            $path = '/' . $imgDir . '/' . $item->getId() . '.png';
            try {
                $link = $dropbox->getTemporaryLink($path);
                $links[$item->getId()] = $link;
            } catch (DropboxClientException $e) {
                // si le fichier n'existe pas, on ne fait rien
                // ou on peut ajouter un message d'erreur pour indiquer que le fichier n'existe pas
                // $links[$item->getId()] = 'Fichier non trouvé';
            }
        }

        return $links;
    }
}