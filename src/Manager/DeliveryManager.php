<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Delivery;
use App\Model\NewDeliveryModel;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use Symfony\Bundle\SecurityBundle\Security;

class DeliveryManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private UserRepository $userRepository
    )
    {
    }

    public function createFrom(NewDeliveryModel $model): Delivery {

        $email = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);
        
        $d = new Delivery();

        $d->setPickupAddress($model->pickupAddress);
        $d->setSenderPhone($model->senderPhone);
        $d->setDeliveryAddress($model->deliveryAddress);
        $d->setRecipientPhone($model->recipientPhone);
        $d->setType($model->type);
        $d->setDescription($model->description);
        $d->setDeliveryDate($model->deliveryDate);
        $d->setTownship($model->township);
        $d->setZone($model->zone);
        $d->setRecipient($model->recipient);
        $d->setCustomer($model->customer);
        $d->setCreatedAt(new \DateTimeImmutable('now'));
        $d->setCreatedBy($user->getId());
        $d->setTrackingNumber($this->generateTrackingNumber($model->type, $model->deliveryDate));


        $this->em->persist($d);
        $this->em->flush();

        return $d;
    }

    private function generateTrackingNumber(string $type, \DateTimeImmutable $deliveryDate): string
    {
        $prefix = ($type === Delivery::TYPE_PACKAGE) ? 'PA-' : (($type === Delivery::TYPE_MAIL) ? 'MA-' : '');

        $datePart = $deliveryDate->format('dmy');

        $timePart = $deliveryDate->format('Hi');

        $randomLetters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3));

        return  "{$prefix}{$datePart}{$timePart}{$randomLetters}";
    }

    private function findDelivery(string $deliveryId): Delivery 
    {
        $delivery = $this->em->find(Delivery::class, $deliveryId);

        if (null === $delivery) {
            throw new UnavailableDataException(sprintf('cannot find delivery with id: %s', $deliveryId));
        }

        return $delivery; 
    }

}