<?php

declare(strict_types=1);

use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Delivery;
use App\Model\Ressource;
use App\Entity\Recipient;
use App\Entity\Notification;
use App\Entity\DeliveryModel;
use App\Entity\DeliveryPerson;

return static function (): iterable {

    yield Ressource::new("user", User::class, "US", true);
    yield Ressource::new("profile", Profile::class, "PR", true);
    yield Ressource::new("recipient", Recipient::class, "RE", true);
    yield Ressource::new("delivery", Delivery::class, "DE", true);
    yield Ressource::new("delivery_model", DeliveryModel::class, "DM", true);
    yield Ressource::new("delivery_person", DeliveryPerson::class, "DP", true);
    yield Ressource::new("notification", Notification::class, "NF", true);
};
