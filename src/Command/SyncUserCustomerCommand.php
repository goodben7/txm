<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'txm:sync-user-customer',
    description: 'Synchronize userId in Customer and holderId in User tables using email as common key',
)]
class SyncUserCustomerCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run in simulation mode without making changes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');

        if ($isDryRun) {
            $io->note('Running in dry-run mode. No changes will be made.');
        }

        $io->title('Starting User-Customer synchronization');

        // Get customers without userId but with email
        $customersToUpdate = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Customer::class, 'c')
            ->where('c.userId IS NULL')
            ->andWhere('c.email IS NOT NULL')
            ->andWhere('c.deleted = :deleted')
            ->setParameter('deleted', false)
            ->getQuery()
            ->getResult();

        // Get users without holderId but with email
        $usersToUpdate = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.holderId IS NULL')
            ->andWhere('u.email IS NOT NULL')
            ->andWhere('u.deleted = :deleted')
            ->setParameter('deleted', false)
            ->getQuery()
            ->getResult();

        $io->section('Updating Customers with missing userId');
        $customerUpdates = 0;
        $customerErrors = 0;

        foreach ($customersToUpdate as $customer) {
            $email = $customer->getEmail();
            $io->text("Processing customer with email: {$email}");

            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $email,
                'deleted' => false
            ]);

            if ($user) {
                $io->text(" - Found matching user with ID: {$user->getId()}");
                
                if (!$isDryRun) {
                    $customer->setUserId($user->getId());
                    $this->entityManager->persist($customer);
                    $customerUpdates++;
                } else {
                    $customerUpdates++;
                }
            } else {
                $io->warning(" - No matching user found for email: {$email}");
                $customerErrors++;
            }
        }

        $io->section('Updating Users with missing holderId');
        $userUpdates = 0;
        $userErrors = 0;

        foreach ($usersToUpdate as $user) {
            $email = $user->getEmail();
            $io->text("Processing user with email: {$email}");

            $customer = $this->entityManager->getRepository(Customer::class)->findOneBy([
                'email' => $email,
                'deleted' => false
            ]);

            if ($customer) {
                $io->text(" - Found matching customer with ID: {$customer->getId()}");
                
                if (!$isDryRun) {
                    $user->setHolderId($customer->getId());
                    $this->entityManager->persist($user);
                    $userUpdates++;
                } else {
                    $userUpdates++;
                }
            } else {
                $io->warning(" - No matching customer found for email: {$email}");
                $userErrors++;
            }
        }

        // Flush changes if not in dry-run mode
        if (!$isDryRun && ($customerUpdates > 0 || $userUpdates > 0)) {
            $this->entityManager->flush();
            $io->success('Changes have been saved to the database.');
        }

        // Summary
        $io->section('Synchronization Summary');
        $io->table(
            ['Entity', 'Processed', 'Updated', 'Errors'],
            [
                ['Customers', count($customersToUpdate), $customerUpdates, $customerErrors],
                ['Users', count($usersToUpdate), $userUpdates, $userErrors],
            ]
        );

        return Command::SUCCESS;
    }
}