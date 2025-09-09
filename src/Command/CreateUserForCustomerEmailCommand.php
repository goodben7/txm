<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Customer;
use App\Entity\Profile;
use App\Manager\UserManager;
use App\Model\UserProxyIntertace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'txm:create-user-for-customer-email',
    description: 'Create a user for a specific customer identified by email',
)]
class CreateUserForCustomerEmailCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserManager $userManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email of the customer')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run in simulation mode without making changes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $isDryRun = $input->getOption('dry-run');

        if ($isDryRun) {
            $io->note('Running in dry-run mode. No changes will be made.');
        }

        $io->title(sprintf('Creating user for customer with email: %s', $email));

        // Check if customer exists
        $customer = $this->entityManager->getRepository(Customer::class)->findOneBy([
            'email' => $email,
            'deleted' => false
        ]);

        if (!$customer) {
            $io->error(sprintf('No customer found with email: %s', $email));
            return Command::FAILURE;
        }

        // Check if customer already has a user
        if ($customer->getUserId() !== null) {
            $io->warning(sprintf('Customer with email %s already has an associated user (ID: %s)', $email, $customer->getUserId()));
            return Command::SUCCESS;
        }

        // Get the sender profile
        $senderProfile = $this->entityManager->getRepository(Profile::class)->findOneBy([
            'personType' => UserProxyIntertace::PERSON_SENDER
        ]);

        if (!$senderProfile) {
            $io->error('Could not find a profile with person type "SENDER". Please create this profile first.');
            return Command::FAILURE;
        }

        try {
            // Create a new user for this customer
            $user = new User();
            $user->setEmail($customer->getEmail());
            $user->setPhone($customer->getPhone());
            $user->setDisplayName($customer->getFullname());
            $user->setPersonType(UserProxyIntertace::PERSON_SENDER);
            $user->setProfile($senderProfile);
            $user->setHolderId($customer->getId());
            $user->setCreatedAt(new \DateTimeImmutable());
            // Use customer's email as default password
            $user->setPlainPassword($customer->getEmail());
            
            if (!$isDryRun) {
                // Create the user
                $this->userManager->create($user);
                
                // Update the customer with the new userId
                $customer->setUserId($user->getId());
                $this->entityManager->persist($customer);
                $this->entityManager->flush();
                
                $io->success(sprintf('Created user with ID: %s and updated customer', $user->getId()));
            } else {
                $io->text('Would create user and update customer (dry run)');
                $io->success('Dry run completed successfully');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error creating user for customer %s: %s', $customer->getId(), $e->getMessage()));
            return Command::FAILURE;
        }
    }
}