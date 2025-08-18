<?php

namespace App\Message\Command;

use App\Model\UpdateStoreModel;

class UpdateStoreCommand extends UpdateStoreModel implements CommandInterface {

    public function __construct(
        public ?string $StoreId = null
    )
    {
    }
}