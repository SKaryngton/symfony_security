<?php

namespace App\Factory;

use App\Entity\CheeseListing;
use App\Repository\CheeseListingRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<CheeseListing>
 *
 * @method        CheeseListing|Proxy create(array|callable $attributes = [])
 * @method static CheeseListing|Proxy createOne(array $attributes = [])
 * @method static CheeseListing|Proxy find(object|array|mixed $criteria)
 * @method static CheeseListing|Proxy findOrCreate(array $attributes)
 * @method static CheeseListing|Proxy first(string $sortedField = 'id')
 * @method static CheeseListing|Proxy last(string $sortedField = 'id')
 * @method static CheeseListing|Proxy random(array $attributes = [])
 * @method static CheeseListing|Proxy randomOrCreate(array $attributes = [])
 * @method static CheeseListingRepository|RepositoryProxy repository()
 * @method static CheeseListing[]|Proxy[] all()
 * @method static CheeseListing[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static CheeseListing[]|Proxy[] createSequence(array|callable $sequence)
 * @method static CheeseListing[]|Proxy[] findBy(array $attributes)
 * @method static CheeseListing[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static CheeseListing[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class CheeseListingFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'description' => self::faker()->words(2,true),
            'owner' => UserFactory::random(),
            'price' => self::faker()->numberBetween(20,1000),
            'title' => self::faker()->word(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(CheeseListing $cheeseListing): void {})
        ;
    }

    protected static function getClass(): string
    {
        return CheeseListing::class;
    }
}
