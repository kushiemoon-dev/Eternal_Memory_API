<?php

namespace App\DataFixtures;

use App\Entity\Card;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // create 20 cards test!
        for ($i = 0; $i < 20; $i++) {
            $card = new Card();
            $card->setTittle('Card ' . $i);
            $card->setDescription('Description of card ' . $i);
            $manager->persist($card);
        }

        $manager->flush();
    }
}
