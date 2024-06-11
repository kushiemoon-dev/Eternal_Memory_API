<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        // Creation basic user"
        $user = new User();
        $user->setEmail("user@eternalapi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);
        
        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@eternalapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

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