<?php

namespace App\DataFixtures;

use App\Entity\FoodEntry;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $entry = new FoodEntry();
        $entry->setName("Bread");
        $entry->setEatDate(new \DateTime());
        $entry->setCalories(500);
        $entry->setSkipDiet(false);
        $entry->setUser(1);
        $manager->persist($entry);

        $manager->flush();
    }
}
