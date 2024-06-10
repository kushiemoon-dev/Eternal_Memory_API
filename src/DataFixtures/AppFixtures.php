<?php

namespace App\DataFixtures;

<<<<<<< HEAD
use App\Entity\Card;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
=======
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
>>>>>>> first_function_and_data

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
<<<<<<< HEAD
        // create 20 cards test!
        for ($i = 0; $i < 20; $i++) {
            $card = new Card();
            $card->setTittle('Card ' . $i);
            $card->setDescription('Description of card ' . $i);
            $manager->persist($card);
        }
=======
        // $product = new Product();
        // $manager->persist($product);
>>>>>>> first_function_and_data

        $manager->flush();
    }
}
