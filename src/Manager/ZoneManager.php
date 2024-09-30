<?php

namespace App\Manager;

use App\Entity\Zone;
use App\Model\NewZOneModel;
use Doctrine\ORM\EntityManagerInterface;

class ZoneManager
{
    public function __construct(
        private EntityManagerInterface $em, 
    )
    {
    }

    public function createFrom(NewZOneModel $model): Zone {

        $zone = new Zone();
    
        $zone->setLabel($model->label);
        $zone->setDescription($model->description);
        $zone->setActived($model->actived);

        foreach ($model->townships as $township) {
            $zone->addTownship($township);
        }

        $this->em->persist($zone);
        $this->em->flush();
        
        return $zone;
    }

}