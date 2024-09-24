<?php
namespace App\Doctrine;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;

class IdGenerator extends AbstractIdGenerator 
{
    public function generateId(EntityManagerInterface $em, object|null $entity): mixed
    {
        $currentDateTime = new \DateTime();
        $dateTimeString = $currentDateTime->format('mdHis');
        $randomLetters = $this->generateRandomLetters(4);
        return $entity::ID_PREFIX . strtoupper($randomLetters . $dateTimeString);
    }

    private function generateRandomLetters(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $randomLetters = '';
        for ($i = 0; $i < $length; $i++) {
            $randomLetters .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $randomLetters;
    }
}