<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Manager\OrderManager;
use App\Model\NewOrderModel;
use App\Model\OrderEstimateResponse;

class EstimateOrderProcessor implements ProcessorInterface
{
    public function __construct(private OrderManager $manager)
    {
    }

    /**
     * @param \App\Dto\CreateOrderDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $model = new NewOrderModel(
            $data->orderItems,
            $data->userId,
            $data->deliveryAddress,
            $data->description
        );
 
        // Get the estimated order but convert it to a response object to avoid IRI generation issues
        $estimatedOrder = $this->manager->estimate($model);

        return OrderEstimateResponse::fromOrder($estimatedOrder);
    }
}