<?php

namespace App\Message\Command;

use App\Model\DeliveryModelInterface;

class CreateDeliveryCommand implements CommandInterface 
{
    public function __construct(public DeliveryModelInterface $deliveryModel){}

}