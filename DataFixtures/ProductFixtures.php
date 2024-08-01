<?php
namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Manufacturer;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Persister\DoctrineEntityPersister;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Inject;

class ProductFixtures extends Fixture
{
    #[Inject('doctrine.orm.entity_manager')]
    private ObjectManager $entityManager;

    public function load(ObjectManager $manager): void
    {
        $loader = new NativeLoader();
        $persister = new DoctrineEntityPersister($manager);

        $fixtures = $loader->loadFile(__DIR__.'/product.yaml');
        $persister->persist($fixtures->getObjects());
    }
}
