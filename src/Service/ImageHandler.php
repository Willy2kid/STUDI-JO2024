<?php

namespace App\Service;
use Symfony\Component\HttpKernel\KernelInterface;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use GuzzleHttp\Client;

class ImageHandler
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    // public function uploadImage($image, $productId)
    // {
    //     (new UploadApi())->upload($image->getPathname(), array(
    //         'folder' => 'product',
    //         'public_id' => $productId
    //     ));
    // }

    public function uploadImage($image, $productId)
    {
        $uploadApi = new UploadApi();
        $result = $uploadApi->upload($image->getPathname(), array(
            'folder' => 'product',
            'public_id' => $productId
        ));
    
        // Get the URL of the uploaded image
        $imageUrl = $result['secure_url'];
    
        // Return the URL
        return $imageUrl;
    }
}