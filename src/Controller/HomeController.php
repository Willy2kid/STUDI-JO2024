<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ImageHandler;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(Request $request, ProductRepository $productRepository, ImageHandler $imageHandler): Response
    {
        $products = $productRepository->findAll();

        if (getenv('APP_ENV') == 'prod') {
            $dropboxAccessToken = $request->getSession()->get('dropbox_access_token');
            $productsImgLinks = $imageHandler->getImageLink('product', $products, $dropboxAccessToken);
        }

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'productsImgLinks' => $productsImgLinks ?? [],
        ]);
    }
}
