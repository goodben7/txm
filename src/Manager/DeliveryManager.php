<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Delivery;
use App\Model\NewDeliveryModel;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use Symfony\Bundle\SecurityBundle\Security;
use App\Exception\InvalidActionInputException;

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

    public function cancel(Delivery $delivery, string $message) : Delivery
    {

        if ( !in_array($delivery->getStatus(), [Delivery::STATUS_PENDING, Delivery::STATUS_VALIDATED, Delivery::STATUS_PICKUPED, Delivery::STATUS_INPROGRESS, Delivery::STATUS_DELAYED])){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $email = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        $delivery->setStatus(Delivery::STATUS_CANCELED);
        $delivery->setMessage($message);
        $delivery->setCanceledAt(new \DateTimeImmutable('now'));
        $delivery->setCanceledBy($user->getId());

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery; 

    }

    public function validate(Delivery $delivery) : Delivery
    {

        if ( !in_array($delivery->getStatus(), [Delivery::STATUS_PENDING, Delivery::STATUS_DELAYED])){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $email = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        $delivery->setStatus(Delivery::STATUS_VALIDATED);
        $delivery->setValidatedAt(new \DateTimeImmutable('now'));
        $delivery->setValidatedBy($user->getId());

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery; 

    }

    public function pickup(Delivery $delivery) : Delivery
    {

        if($delivery->getStatus() != Delivery::STATUS_VALIDATED){
            throw new InvalidActionInputException('Action not allowed : invalid delivery state'); 
        }

        $email = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

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

        $email = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

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

        $email = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

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

        $email = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        $delivery->setStatus(Delivery::STATUS_TERMINATED);
        $delivery->setTerminedAt(new \DateTimeImmutable('now'));
        $delivery->setTerminedBy($user->getId());

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery; 

    }

}