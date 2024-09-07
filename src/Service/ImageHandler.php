<?php

namespace App\Service;
use Symfony\Component\HttpKernel\KernelInterface;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use GuzzleHttp\Client;

class ImageHandler
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
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
        // $accessToken = getenv('DROPBOX_ACCESS_TOKEN');
        $accessToken = $this->getAccessToken();
        $dropboxApp = new DropboxApp("t03ew4kslhdea50", "lzizv35rwznpive", $accessToken);
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

    private function getAccessToken(){
        $appKey = 't03ew4kslhdea50';
        $appSecret = 'lzizv35rwznpive';
        $accessToken = 'pbaDUVvIPpkAAAAAAAAAGYZREap7SZQYcemuktNzr48';

        $client = new Client();

        $response = $client->post('https://api.dropboxapi.com/oauth2/token', [
            'auth' => [$appKey, $appSecret],
            'form_params' => [
                'code' => $accessToken,
                'grant_type' => 'authorization_code',
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        // Vérifiez si la réponse est réussie
        if ($response->getStatusCode() === 200) {
            $responseData = json_decode($response->getBody()->getContents(), true);
            $token = $responseData['access_token'];
        
            return $token;
        } else {
            echo "Erreur lors de la récupération du token d'accès";
        }
    }
}