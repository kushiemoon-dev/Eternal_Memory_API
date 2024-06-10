<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\Type;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // creation of 10 types.
        $listType = [];
        for ($i = 0; $i < 10; $i++) {
         // creation type itself.
        $type = new Type();
        $type->setFirstName("Type first " . $i);
        $type->setLastName("Type second " . $i);
        $manager->persist($type);
        // On sauvegarde l'auteur créé dans un tableau.
        $listType[] = $type;

        }

        // creation of 20 cards
    for ($i = 0; $i < 20; $i++) {
        $card = new Card();
        $card->setTittle("Card" . $i);
        $card->setDescription("Description of card : " . $i);

        // linking randomly on the array.
         
        $card->setType($listType[array_rand($listType)]);
        $manager->persist($card);

        $manager->flush();

    }
} 
  
}