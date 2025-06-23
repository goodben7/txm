<?php

namespace App\Manager;

use App\Entity\DeliveryPerson;
use App\Model\UserProxyIntertace;
use App\Service\PasswordGenerator;
use App\Model\NewDeliveryPersonModel;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\Command\CreateUserCommand;
use App\Exception\UnavailableDataException;
use App\Message\Command\CommandBusInterface;
use App\Exception\UnauthorizedActionException;

class DeliveryPersonManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProfileRepository $profileRepository,
        private CommandBusInterface $bus, 
        private PasswordGenerator $passwordGenerator
    )
    {
    }

    public function createDeliveryPerson(NewDeliveryPersonModel $model)
    {
        $deliveryPerson = new DeliveryPerson();
        $deliveryPerson->setFullname($model->fullname);
        $deliveryPerson->setPhone($model->phone);
        $deliveryPerson->setEmail($model->email);
        $deliveryPerson->setStatus(DeliveryPerson::STATUS_ACTIVE);
        $deliveryPerson->setVehicleType($model->vehicleType);
        $deliveryPerson->setLicenseNumber($model->licenseNumber);
        $deliveryPerson->setVehicleLicensePlate($model->vehicleLicensePlate);
        $deliveryPerson->setIdentificationNumber($this->generateIdentificationNumber($model->vehicleType, new \DateTimeImmutable()));
        $deliveryPerson->setCountry($model->country);
        $deliveryPerson->setAddress($model->address);
        $deliveryPerson->setDateOfBirth($model->dateOfBirth);
        $deliveryPerson->setCity($model->city);
        $deliveryPerson->setCreatedAt(new \DateTimeImmutable());
    

        try {
            $this->em->persist($deliveryPerson);
    
        } catch (\Exception $e) {
            throw new UnavailableDataException($e->getMessage());
        }

        $profile = $this->profileRepository->findOneBy(['personType' => UserProxyIntertace::PERSON_DLV_PRS]);

        if (null === $profile) {
            throw new UnavailableDataException('cannot find profile');
        }

        $user = $this->bus->dispatch(
            new CreateUserCommand(
                $deliveryPerson->getEmail(),
                $deliveryPerson->getPhone() ?? $this->passwordGenerator->generateDefaultPassword(),
                $profile,
                $deliveryPerson->getPhone(),
                $deliveryPerson->getFullname()
            )
        );

        $deliveryPerson->setUserId($user->getId());

        $this->em->persist($deliveryPerson);
        $this->em->flush();

        return $deliveryPerson;
        
    }

    private function generateIdentificationNumber(string $type, \DateTimeImmutable $createdAt): string
    {
        $prefix = 'DE';

        $datePart = $createdAt->format('dmy');

        $timePart = $createdAt->format('Hi');

        $randomLetters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3));

        return  "{$prefix}{$datePart}{$timePart}{$randomLetters}";
    }

    public function delete(string $deliveryPersonId) 
    {
        $deliveryPerson = $this->findDeliveryPerson($deliveryPersonId);

        if ($deliveryPerson->isDeleted()) {
            throw new UnauthorizedActionException('this action is not allowed');
        }

        $deliveryPerson->setDeleted(true);
        $deliveryPerson->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->em->persist($deliveryPerson);
        $this->em->flush();
    }

    private function findDeliveryPerson(string $deliveryPersonId): DeliveryPerson 
    {
        $deliveryPerson = $this->em->find(DeliveryPerson::class, $deliveryPersonId);

        if (null === $deliveryPerson) {
            throw new UnavailableDataException(sprintf('cannot find Delivery Person with id: %s', $deliveryPersonId));
        }

        return $deliveryPerson; 
    }
}