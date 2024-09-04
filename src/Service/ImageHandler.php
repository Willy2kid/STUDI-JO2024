<?php

namespace App\Service;
use Symfony\Component\HttpKernel\KernelInterface;

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

        $image->move($productImgDir, $imageName);
        
    }
}