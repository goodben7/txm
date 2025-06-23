<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Delivery;
use App\Entity\DeliveryPerson;
use App\Model\NewDeliveryModel;
use App\Model\UpdateDeliveryModel;
use App\Repository\UserRepository;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use Symfony\Bundle\SecurityBundle\Security;
use App\Exception\InvalidActionInputException;

class DeliveryManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private UserRepository $userRepository,
        private QueryBusInterface $queries,
        private ActivityEventDispatcher $eventDispatcher,
    )
    {
    }

    public function createFrom(NewDeliveryModel $model): Delivery {

        $userId = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null;

        /** @var User $user */
        $user = $this->queries->ask(new GetUserDetails($userId));
        
        $d = new Delivery();

        $d->setType($model->type);
        $d->setDescription($model->description);
        $d->setDeliveryDate($model->deliveryDate);
        $d->setRecipient($model->recipient);
        $d->setCustomer($model->customer);
        $d->setCreatedAt(new \DateTimeImmutable('now'));
        $d->setCreatedBy($user ? $user->getId() : 'SYSTEM');
        $d->setPickupAddress($model->pickupAddress);
        $d->setDeliveryAddress($model->deliveryAddress);
        $d->setAdditionalInformation($model->additionalInformation);
        $d->setTrackingNumber($this->generateTrackingNumber($model->type, $model->deliveryDate));
        $d->setTownship($model->deliveryAddress ? $model->deliveryAddress->getTownship()?->getId() : null);
        $d->setZone($model->deliveryAddress ? $model->deliveryAddress->getTownship()?->getZone()?->getId() : null);
        $d->setCreatedFrom($model->createdFrom);
        $d->setCreatedByTypePerson($user ? $user?->getPersonType() : null);
        
        $this->em->persist($d);
        $this->em->flush();
        
        return $d;
    }

    public function updateFrom(UpdateDeliveryModel $model, string $deliveryId): Delivery {

        $userId = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null;
    
        /** @var User $user */
        $user = $this->queries->ask(new GetUserDetails($userId));
        
        $d = $this->findDelivery($deliveryId);
    
        $d->setType($model->type ?? $d->getType());
        $d->setDescription($model->description ?? $d->getDescription());
        $d->setRecipient($model->recipient ?? $d->getRecipient());
        $d->setCustomer($model->customer ?? $d->getCustomer());
        $d->setUpdatedAt(new \DateTimeImmutable('now'));
        $d->setUpdatedBy($user ? $user->getId() : 'SYSTEM');
        $d->setPickupAddress($model->pickupAddress ?? $d->getPickupAddress());
        $d->setDeliveryAddress($model->deliveryAddress ?? $d->getDeliveryAddress());
        $d->setAdditionalInformation($model->additionalInformation ?? $d->getAdditionalInformation());
        $d->setTownship(
            $model->deliveryAddress && $model->deliveryAddress->getTownship() 
                ? $model->deliveryAddress->getTownship()->getId() 
                : $d->getTownship()
        );
        $d->setZone(
            $model->deliveryAddress && $model->deliveryAddress->getTownship() && $model->deliveryAddress->getTownship()->getZone() 
                ? $model->deliveryAddress->getTownship()->getZone()->getId() 
                : $d->getZone()
        );
        
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

    public function cancel(Delivery $delivery, string $message) : Delivery
    {

        if ( !in_array($delivery->getStatus(), [Delivery::STATUS_PENDING, Delivery::STATUS_VALIDATED, Delivery::STATUS_PICKUPED, Delivery::STATUS_INPROGRESS, Delivery::STATUS_DELAYED])){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $delivery->setStatus(Delivery::STATUS_CANCELED);
        $delivery->setMessage($message);
        $delivery->setCanceledAt(new \DateTimeImmutable('now'));
        $delivery->setCanceledBy($user->getId());

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery; 

    }

    public function validate(Delivery $delivery, DeliveryPerson $deliveryPerson) : Delivery
    {

        if ( !in_array($delivery->getStatus(), [Delivery::STATUS_PENDING, Delivery::STATUS_DELAYED])){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $delivery->setStatus(Delivery::STATUS_VALIDATED);
        $delivery->setValidatedAt(new \DateTimeImmutable('now'));
        $delivery->setValidatedBy($user->getId());
        $delivery->setDeliveryPerson($deliveryPerson);

        $this->em->persist($delivery);
        $this->em->flush();

        $this->eventDispatcher->dispatch($delivery, Delivery::EVENT_DELIVERY_VALIDATED);

        return $delivery; 

    }

    public function pickup(Delivery $delivery) : Delivery
    {

        if($delivery->getStatus() != Delivery::STATUS_VALIDATED){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $delivery->setStatus(Delivery::STATUS_PICKUPED);
        $delivery->setPickupedAt(new \DateTimeImmutable('now'));
        $delivery->setPickupedBy($user->getId());

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery; 

    }

    public function inprogress(Delivery $delivery) : Delivery
    {

        if($delivery->getStatus() != Delivery::STATUS_PICKUPED){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $delivery->setStatus(Delivery::STATUS_INPROGRESS);
        $delivery->setInprogressAt(new \DateTimeImmutable('now'));
        $delivery->setInprogressBy($user->getId());

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery; 

    }

    public function delay(Delivery $delivery, \DateTimeImmutable $delayedAt, string $message) : Delivery
    {

        if ( !in_array($delivery->getStatus(), [Delivery::STATUS_VALIDATED, Delivery::STATUS_PICKUPED, Delivery::STATUS_INPROGRESS])){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $delivery->setStatus(Delivery::STATUS_DELAYED);
        $delivery->setMessage($message);
        $delivery->setDeliveryDate($delayedAt);
        $delivery->setDelayedAt($delayedAt);
        $delivery->setDelayedBy($user->getId());

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery; 

    }

    public function finish(Delivery $delivery) : Delivery
    {

        if($delivery->getStatus() != Delivery::STATUS_INPROGRESS){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $delivery->setStatus(Delivery::STATUS_TERMINATED);
        $delivery->setTerminedAt(new \DateTimeImmutable('now'));
        $delivery->setTerminedBy($user->getId());

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery; 

    }

    public function reassigne(Delivery $delivery, DeliveryPerson $deliveryPerson, string $message) : Delivery
    {

        if ( !in_array($delivery->getStatus(), [Delivery::STATUS_VALIDATED, Delivery::STATUS_DELAYED, Delivery::STATUS_PICKUPED])){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $delivery->setStatus(Delivery::STATUS_VALIDATED);
        $delivery->setReassignedAt(new \DateTimeImmutable('now'));
        $delivery->setReassignedBy($user->getId());
        $delivery->setMessage($message);
        $delivery->setDeliveryPerson($deliveryPerson);

        $this->em->persist($delivery);
        $this->em->flush();

        $this->eventDispatcher->dispatch($delivery, Delivery::EVENT_DELIVERY_REASSIGNED);

        return $delivery; 

    }

}