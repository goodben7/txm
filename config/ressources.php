<?php

declare(strict_types=1);

use App\Entity\User;
use App\Entity\Profile;
use App\Model\Ressource;
use App\Entity\Recipient;

return static function (): iterable {

    yield Ressource::new("user", User::class, "US", true);
    yield Ressource::new("profile", Profile::class, "PR", true);
    yield Ressource::new("recipient", Recipient::class, "RE", true);
};
