<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProductRepository $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    public function getProducts(): Response
    {
        $products = $this->productRepository->findAll();
        
        return $this->json([
            'data' => $products
        ], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return Response
     */
    #[Route('/{id}', methods: ['GET'])]
    public function getProduct(string $id): Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json([
                'data' => [
                    'error' => 'Not found product by id ' . $id
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => $product
        ], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    public function createProduct(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);
        
        $product = new Product();
        $product->setName($requestData['name'] ?? '');
        $product->setDescription($requestData['description'] ?? '');
        $product->setPrice($requestData['price'] ?? 0);
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        return $this->json([
            'data' => $product
        ], Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return Response
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function updateProduct(Request $request, string $id): Response
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            return $this->json([
                'data' => [
                    'error' => 'Not found product by id ' . $id
                ]
            ], Response::HTTP_NOT_FOUND);
        }
        
        $requestData = json_decode($request->getContent(), true);
        
        if (isset($requestData['name'])) {
            $product->setName($requestData['name']);
        }
        
        if (isset($requestData['description'])) {
            $product->setDescription($requestData['description']);
        }
        
        if (isset($requestData['price'])) {
            $product->setPrice($requestData['price']);
        }
        
        $this->entityManager->flush();
        
        return $this->json([
            'data' => $product
        ], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return Response
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteProduct(string $id): Response
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            return $this->json([
                'data' => [
                    'error' => 'Not found product by id ' . $id
                ]
            ], Response::HTTP_NOT_FOUND);
        }
        
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}