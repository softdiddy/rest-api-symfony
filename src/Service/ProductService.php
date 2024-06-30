<?php
namespace App\Service;

use App\Entity\Products;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    private $entityManager;
    private $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    public function createProduct(string $name, string $description): Products
    {
        $product = new Products();
        $product->setName($name);
        $product->setDescription($description);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function updateProduct(Products $product, string $name, string $description): Products
    {
        $product->setName($name);
        $product->setDescription($description);

        $this->entityManager->flush();

        return $product;
    }

    public function deleteProduct(Products $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    public function getProduct(int $id): ?Products
    {
        return $this->productRepository->find($id);
    }

    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }

    // Add other business logic methods as needed
}
