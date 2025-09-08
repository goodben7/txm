<?php

namespace App\Service;

use App\Model\UserProxyIntertace;
use Doctrine\ORM\EntityManagerInterface;

class CodeGeneratorService
{
    private const array ENTITY_PREFIXES = [
        'User' => [
            UserProxyIntertace::PERSON_ADMIN => 'AD',
            UserProxyIntertace::PERSON_SENDER => 'SN',
            UserProxyIntertace::PERSON_DLV_PRS => 'DP',
            UserProxyIntertace::PERSON_CUSTOMER => 'CU',
        ],
        'Customer' => [
            UserProxyIntertace::PERSON_SENDER => 'CU',
        ],
        'Recipient' => [
            UserProxyIntertace::PERSON_CUSTOMER => 'RE',
        ],
        'DeliveryPerson' => [
            UserProxyIntertace::PERSON_DLV_PRS => 'DP',
        ],
    ];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @param string $entityName 
     * @param string $personType 
     * @return string 
     */
    public function generateCode(string $entityName, string $personType): string
    {

        if (!isset(self::ENTITY_PREFIXES[$entityName][$personType])) {
            throw new \InvalidArgumentException("The combination of entity '$entityName' and person type '$personType' is not supported." );
        }

        $prefix = self::ENTITY_PREFIXES[$entityName][$personType];
        
        $lastCode = $this->findLastCodeByPrefix($prefix);
        
    
        $nextNumber = 1;
        if ($lastCode) {
            $numericPart = (int) substr($lastCode, 2, 3);
            $nextNumber = $numericPart + 1;
        }
        
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $prefix 
     * @return string|null 
     */
    private function findLastCodeByPrefix(string $prefix): ?string
    {
        $conn = $this->entityManager->getConnection();
        
        $sql = "SELECT code FROM user WHERE code LIKE :prefix AND LENGTH(code) = 5 ORDER BY code DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('prefix', "{$prefix}%");
        $result = $stmt->executeQuery()->fetchOne();
        
        if ($result) {
            return $result;
        }
        
        return null;
    }

    /**
     * @param string $code 
     * @return bool 
     */
    public function codeExists(string $code): bool
    {
        $conn = $this->entityManager->getConnection();
        
        $sql = "SELECT COUNT(id) FROM user WHERE code = :code";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('code', $code);
        $count = $stmt->executeQuery()->fetchOne();
        
        return $count > 0;
    }
}