<?php
namespace App\Controller;

use App\Entity\Product;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api', name: 'api_')]
class ProductController extends AbstractController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/products', name: 'products_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts();

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/products', name: 'products_create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;

        // Validate input
        $errors = $validator->validate([
            'name' => $name,
            'description' => $description,
        ], new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
        ]));

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        // Create the product
        $product = $this->productService->createProduct($name, $description);

        // Return success response
        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
        ], 201);
    }

    #[Route('/products/{id}', name: 'product_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
        ]);
    }

    #[Route('/products/{id}', name: 'product_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id, ValidatorInterface $validator): JsonResponse
    {
        $product = $this->productService->getProduct($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;

        // Validate input
        $errors = $validator->validate([
            'name' => $name,
            'description' => $description,
        ], new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
        ]));

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        // Update the product
        $updatedProduct = $this->productService->updateProduct($product, $name, $description);

        // Return success response
        return $this->json([
            'id' => $updatedProduct->getId(),
            'name' => $updatedProduct->getName(),
            'description' => $updatedProduct->getDescription(),
        ]);
    }

    #[Route('/products/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $this->productService->deleteProduct($product);

        return $this->json(['message' => 'Product deleted successfully']);
    }
}
