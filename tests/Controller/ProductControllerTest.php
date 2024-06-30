<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    private static $client;
    private static $jwtToken;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
        self::$jwtToken = self::getJwtToken();
    }

    private static function getJwtToken(): string
    {
        $client = self::$client;

        $authPayload = [
            'email' => 'soft@gmail.com',
            'password' => '123456',
        ];

        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($authPayload)
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        self::assertTrue($response->isSuccessful(), 'Authentication request failed.');
        self::assertArrayHasKey('token', $data, 'JWT token not found in the response.');

        return $data['token'];
    }

    public function testCreateProduct(): void
    {
        $client = self::$client;

        $payload = [
            'name' => 'Test Product',
            'description' => 'Test Product Description',
        ];

        $client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . self::$jwtToken],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testGetAllProducts(): void
    {
        $client = self::$client;

        $client->request('GET', '/api/products', [], [], ['HTTP_Authorization' => 'Bearer ' . self::$jwtToken]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testGetProduct(): void
    {
        $client = self::$client;

        $productId = 1; // Replace with an existing product ID in your database

        $client->request('GET', '/api/products/' . $productId, [], [], ['HTTP_Authorization' => 'Bearer ' . self::$jwtToken]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testUpdateProduct(): void
    {
        $client = self::$client;

        $productId = 1; // Replace with an existing product ID in your database

        $payload = [
            'name' => 'Updated Product Name',
            'description' => 'Updated Product Description',
        ];

        $client->request(
            'PUT',
            '/api/products/' . $productId,
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . self::$jwtToken],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Product Name', $responseData['name']);
    }

    public function testDeleteProduct(): void
    {
        $client = self::$client;

        $productId = 3; 

        $client->request(
        'DELETE', 
        '/api/products/' . $productId, 
        [], 
        [], 
        ['HTTP_Authorization' => 'Bearer ' . self::$jwtToken]);

        $this->assertResponseStatusCodeSame(200);
    }
}

