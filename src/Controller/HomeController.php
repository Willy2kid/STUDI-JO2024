<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ImageHandler;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(ProductRepository $productRepository, ImageHandler $imageHandler): Response
    {
        $products = $productRepository->findAll();

        if (getenv('APP_ENV') == 'prod') {
            $productsImgLinks = $imageHandler->getImageLink('product', $products);
        }

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'productsImgLinks' => $productsImgLinks ?? [],
        ]);
    }
}
