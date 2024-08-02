<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const API_TOKEN = '24234234234234';

    private HttpClientInterface $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();

        //dd($this->client, $this->entityManager);
        $user = new User();
        $user->setEmail('test@mail.com');
        $user->setPassword('secret');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $apiToken = new ApiToken();
        $apiToken->setToken(self::API_TOKEN);
        $apiToken->setUser($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();

    }

    public function testGetCollection(): void
    {
        $response = $this->client->request('GET', '/api/products', [
            'headers' => [
                'x-api-token' => self::API_TOKEN,
            ]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'Content-Type', 'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@id' => '/api/products',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/products?_page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?_page=1',
                'hydra:last' => '/api/products?_page=20',
                'hydra:next' => '/api/products?_page=2',
            ],
        ]);

        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testPagination()
    {
        $response = $this->client->request('GET', '/api/products?_page=2',[
            'headers' => [
                'x-api-token' => self::API_TOKEN,
            ]
        ]);

        $this->assertJsonContains([
            'hydra:view' => [
                '@id' => '/api/products?_page=2',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?_page=1',
                'hydra:last' => '/api/products?_page=20',
                'hydra:previous' => '/api/products?_page=1',
                'hydra:next' => '/api/products?_page=3',
            ],
        ]);
    }

    public function testCreateProduct(): void
    {
        $this->client->request('POST', '/api/products', [
            'headers' => [
                'Content-Type' => 'application/ld+json; charset=utf-8',
                'x-api-token' => self::API_TOKEN,
            ],
            'json' => [
                'name'          => 'Test Product',
                'description'   => 'Test product description',
                'mpn'           => 'TEST_MPN',
                'issueDate'     => '2022-01-01',
                'manufacturer'  => '/api/manufacturers/1',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->assertResponseHeaderSame(
            'Content-Type', 'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            'name'          => 'Test Product',
            'description'   => 'Test product description',
            'mpn'           => 'TEST_MPN',
            'issueDate'     => '2022-01-01T00:00:00+00:00',
        ]);
    }

    public function testUpdateProduct(): void
    {
        $this->client->request('PUT', '/api/products/1',
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                    'x-api-token' => self::API_TOKEN,
                ],
                'json' => [
                    'mpn'          => 'Updated',
                    'name'          => 'Updated Test Product',
                    'description'   => 'Updated test product description',
                    'issueDate'     => '2022-01-01T00:00:00+00:00',
                ],
            ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/products/1',
            'mpn' => 'Updated',
            'name'          => 'Updated Test Product',
            'description'   => 'Updated test product description',
            'issueDate' => '2022-01-01T00:00:00+00:00'
        ]);
    }

    public function testCreateInvalidProduct(): void
    {
        $this->client->request('POST', '/api/products', [
            'headers' => [
                    'Content-Type' => 'application/ld+json; charset=utf-8',
                    'x-api-token' => self::API_TOKEN,
                ],
            'json' => [
                'mpn'          => '1234',
                'name'         => 'A Test Product',
                'description'  => 'A Test Description',
                'issueDate'    => '1985-07-31',
                'manufacturer' => null,
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context'          => '/api/contexts/ConstraintViolationList',
            '@type'             => 'ConstraintViolationList',
            'hydra:title'       => 'An error occurred',
            'hydra:description' => 'manufacturer: This value should not be null.',
        ]);
    }

    public function testInvalidToken(): void
    {
        $this->client->request('PUT', '/api/products/1',
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                    'x-api-token' => 'fake-token',
                ],
                'json' => [
                    'mpn'          => 'Updated',
                    'name'          => 'Updated Test Product',
                    'description'   => 'Updated test product description',
                    'issueDate'     => '2022-01-01T00:00:00+00:00',
                ],
            ]);
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'message' => 'Invalid credentials.'
        ]);
    }
}
