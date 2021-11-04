<?php

namespace App\DataFixtures;

use App\Entity\FoodEntry;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($u=1; $u <= 2; $u++) {
            $date = new \DateTime();
            for ($c = 0; $c < 20; $c++) {
                $this->createEntity($manager, $u, $date, $u == 2? [1000] : [500, 1000, 2000]);
                $date->modify("-1 day");
            }
        }
    }

    protected function createEntity($manager, $user, $date, $calories) {
        $names = ['Bread', 'Meat', 'Pasta', 'Panino', 'Cake', 'Hamburger'];

        $entry = new FoodEntry();
        $entry->setName($names[array_rand($names)]);
        $entry->setEatDate($date);
        $entry->setCalories($calories[array_rand($calories)]);
        $entry->setSkipDiet(false);
        $entry->setUser($user);
        $manager->persist($entry);
        $manager->flush();
    }
}
