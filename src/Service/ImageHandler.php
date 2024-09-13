<?php

namespace App\Service;
use Symfony\Component\HttpKernel\KernelInterface;
use Cloudinary\Api\Upload\UploadApi;
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
        // $productImgDir = $this->kernel->getProjectDir(). '/public/images/product/';
        // $imageName = $productId. '.png'     // 
        // if (!is_dir($productImgDir)) {
        //     mkdir($productImgDir, 0777, true);
        // 
        // // Check if the image already exists
        // $imagePath = $productImgDir . $imageName;
        // if (file_exists($imagePath)) {
        //     unlink($imagePath);
        // 
        // $image->move($productImgDir, $imageName);

        $cloudinary->uploadApi()->upload($image->getPathname(), array(
            'folder' => 'product',
            'public_id' => $productId
        ));
    }

    public function getImageLink($imgDir, $items)
    {
        $appKey = getenv('DROPBOX_API_KEY');
        $appSecret = getenv('DROPBOX_API_SECRET');
        // $accessToken = getenv('DROPBOX_ACCESS_TOKEN');

        $accessToken = $this->getAccessToken($appKey, $appSecret);
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

    private function getAccessToken($appKey, $appSecret){

        $accessCode = $this->getAccessCode($appKey);
        $client = new Client();

        $response = $client->post('https://api.dropboxapi.com/oauth2/token', [
            'auth' => [$appKey, $appSecret],
            'form_params' => [
                'code' => $accessCode,
                'grant_type' => 'authorization_code',
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $responseData = json_decode($response->getBody()->getContents(), true);
            $token = $responseData['access_token'];
        
            return $token;
        } else {
            echo "Erreur lors de la récupération du token d'accès";
        }
    }

    private function getAccessCode($clientId){
        $client = new Client();

        $uri = 'https://www.dropbox.com/oauth2/authorize';
        $queryParams = [
            'client_id' => $clientId,
            'token_access_type' => 'offline',
            'response_type' => 'code',
        ];

        $response = $client->get($uri, ['query' => $queryParams]);

        if ($response->getStatusCode() === 200) {
            $location = $response->getHeader('Location');
            $code = substr($location[0], strpos($location[0], '=') + 1);
        
            return $code;
        } else {
            echo "Erreur lors de la récupération du code d'accès";
        }
    }
}