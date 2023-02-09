<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Factory\CheeseListingFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {

        $user=new User();
        $user->setEmail('dev@example.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user,'dev'));
        $user->setRoles(['ROLE_DEV']);
        $user->setUsername('DEV');
        $user->setLastApiCalledAt(new \DateTimeImmutable());

        $token= new ApiToken($user);
        $manager->persist($user);
        $manager->persist($token);

        UserFactory::createOne(['email'=>'admin@example.com','username'=>'admin','plainPassword'=>'admin','roles'=>['ROLE_ADMIN']]);
        UserFactory::createMany(2);

        CheeseListingFactory::createMany(10);



        $manager->flush();
    }
}
