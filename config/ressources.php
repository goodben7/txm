<?php

declare(strict_types=1);

use App\Entity\User;
use App\Entity\Store;
use App\Entity\Product;
use App\Entity\Profile;
use App\Entity\Service;
use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\Document;
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
    yield Ressource::new("customer", Customer::class, "CU", true);
    yield Ressource::new("document", Document::class, "DO", true);
    yield Ressource::new("service", Service::class, "SE", true);
    yield Ressource::new("store", Store::class, "ST", true);
    yield Ressource::new("product", Product::class, "PD", true);
};
