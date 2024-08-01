<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Fixtures\FixtureLoader;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ProductsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    protected static function getKernelClass(): string
    {
        // Return the fully qualified class name of your kernel class
        return \App\Kernel::class;
    }


    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/api/products');

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
        $response = static::createClient()->request('GET', '/api/products?_page=2');

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
        static::createClient()->request('POST', '/api/products', [
            'headers' => [
                'Content-Type' => 'application/ld+json; charset=utf-8',
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
        $client = static::createClient();

        $client->request('PUT', '/api/products/1',
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
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
        static::createClient()->request('POST', '/api/products', [
            'headers' => [
                    'Content-Type' => 'application/ld+json; charset=utf-8',
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
}
