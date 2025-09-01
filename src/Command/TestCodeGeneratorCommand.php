<?php

namespace App\Command;

use App\Model\UserProxyIntertace;
use App\Service\CodeGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCodeGeneratorCommand extends Command
{
    public function __construct(
        private CodeGeneratorService $codeGeneratorService
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-code-generator')
            ->setDescription('Test the code generator service')
            ->addOption('entity', null, InputOption::VALUE_OPTIONAL, 'Entity name (User, Customer, Recipient, DeliveryPerson)', 'User')
            ->addOption('person-type', null, InputOption::VALUE_OPTIONAL, 'Person type (ADM, SND, DLVPRS, CUST)', UserProxyIntertace::PERSON_ADMIN);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $entityName = $input->getOption('entity');
        $personType = $input->getOption('person-type');
        
        $io->title('Testing Code Generator Service');
        $io->section("Generating code for entity '$entityName' with person type '$personType'");
        
        try {
            $code = $this->codeGeneratorService->generateCode($entityName, $personType);
            $io->success("Generated code: $code");
            
            $exists = $this->codeGeneratorService->codeExists($code);
            $io->note("Code exists in database: " . ($exists ? 'Yes' : 'No'));
            
            // Test all valid combinations
            if ($input->getOption('entity') === 'User' && $input->getOption('person-type') === UserProxyIntertace::PERSON_ADMIN) {
                $this->testAllValidCombinations($io);
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function testAllValidCombinations(SymfonyStyle $io): void
    {
        $io->section('Testing all valid entity and person type combinations');
        
        $validCombinations = [
            ['User', UserProxyIntertace::PERSON_ADMIN],
            ['User', UserProxyIntertace::PERSON_SENDER],
            ['User', UserProxyIntertace::PERSON_DLV_PRS],
            ['User', UserProxyIntertace::PERSON_CUSTOMER],
            ['Customer', UserProxyIntertace::PERSON_SENDER],
            ['Recipient', UserProxyIntertace::PERSON_CUSTOMER],
            ['DeliveryPerson', UserProxyIntertace::PERSON_DLV_PRS],
        ];
        
        $results = [];
        
        foreach ($validCombinations as [$entity, $personType]) {
            try {
                $code = $this->codeGeneratorService->generateCode($entity, $personType);
                $exists = $this->codeGeneratorService->codeExists($code);
                $results[] = [
                    $entity,
                    $personType,
                    $code,
                    $exists ? 'Yes' : 'No'
                ];
            } catch (\Exception $e) {
                $results[] = [$entity, $personType, 'ERROR', $e->getMessage()];
            }
        }
        
        $io->table(
            ['Entity', 'Person Type', 'Generated Code', 'Exists in DB'],
            $results
        );
    }
}