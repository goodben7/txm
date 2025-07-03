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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'txm:create-user-for-customers',
    description: 'Create users for customers that don\'t have one yet',
)]
class CreateUserForCustomersCommand extends Command
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

        $io->title('Creating users for customers without users');

        // Get customers without userId
        $customersWithoutUser = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Customer::class, 'c')
            ->where('c.userId IS NULL')
            ->andWhere('c.deleted = :deleted')
            ->setParameter('deleted', false)
            ->getQuery()
            ->getResult();

        if (empty($customersWithoutUser)) {
            $io->success('All customers already have associated users.');
            return Command::SUCCESS;
        }

        $io->section(sprintf('Found %d customers without users', count($customersWithoutUser)));

        // Get the sender profile
        $senderProfile = $this->entityManager->getRepository(Profile::class)->findOneBy([
            'personType' => UserProxyIntertace::PERSON_SENDER
        ]);

        if (!$senderProfile) {
            $io->error('Could not find a profile with person type "SENDER". Please create this profile first.');
            return Command::FAILURE;
        }

        $createdCount = 0;
        $errorCount = 0;

        foreach ($customersWithoutUser as $customer) {
            $io->text(sprintf('Processing customer: %s (ID: %s)', $customer->getFullname(), $customer->getId()));
            
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
                // Utiliser l'email du client comme mot de passe par défaut
                $user->setPlainPassword($customer->getEmail());
                
                if (!$isDryRun) {
                    // Create the user
                    $this->userManager->create($user);
                    
                    // Update the customer with the new userId
                    $customer->setUserId($user->getId());
                    $this->entityManager->persist($customer);
                    $this->entityManager->flush();
                    
                    $io->text(sprintf(' - Created user with ID: %s and updated customer', $user->getId()));
                } else {
                    $io->text(' - Would create user and update customer (dry run)');
                }
                
                $createdCount++;
            } catch (\Exception $e) {
                $io->error(sprintf(' - Error processing customer %s: %s', $customer->getId(), $e->getMessage()));
                $errorCount++;
            }
        }

        // Summary
        $io->section('Creation Summary');
        $io->table(
            ['Total Customers', 'Users Created', 'Errors'],
            [[
                count($customersWithoutUser),
                $isDryRun ? sprintf('%d (simulated)', $createdCount) : $createdCount,
                $errorCount
            ]]
        );

        if ($createdCount > 0) {
            $io->success(sprintf(
                '%s %d users for customers without users',
                $isDryRun ? 'Would create' : 'Successfully created',
                $createdCount
            ));
        }

        return Command::SUCCESS;
    }
}