<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\OrderItemRepository;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ImageHandler;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin')]
    public function index(ProductRepository $productRepository, OrderItemRepository $orderItemRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'products' => $productRepository->findAll(),
            'counts' => $orderItemRepository->countOrderItemsByProduct(),
        ]);
    }

    #[Route('/nouveau', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageHandler $imageHandler): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $image = $form->get('image')->getData();
            if ($image) {
                $productId = $product->getId();
                $imageUrl = $imageHandler->uploadImage($image, $productId);
                $product->setImg($imageUrl);
                $entityManager->flush();
            }

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/newProduct.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, ImageHandler $imageHandler): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $image = $form->get('image')->getData();
            if ($image) {
                $productId = $product->getId();
                $imageHandler->uploadImage($image, $productId);
            }

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/editProduct.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'product_delete')]
    public function delete(Request $request, Product $product, OrderItemRepository $orderItemRepository, EntityManagerInterface $entityManager): Response
    {
        $orderItemRepository->deleteOrderItemsById($product->getId());
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('admin');
    }
}
