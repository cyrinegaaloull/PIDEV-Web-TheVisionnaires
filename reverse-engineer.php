<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\DBAL\Types\Type;

require_once 'vendor/autoload.php';

// Register ENUM type as string
if (!Type::hasType('enum')) {
    Type::addType('enum', 'Doctrine\\DBAL\\Types\\StringType');
}

$paths = [__DIR__ . '/src/Entity'];
$isDevMode = true;

// Use PHP 8+ attributes instead of annotations
$config = Setup::createAttributeMetadataConfiguration($paths, $isDevMode);

$conn = [
    'dbname' => 'integration1',
    'user' => 'root',
    'password' => '',
    'host' => '127.0.0.1',
    'driver' => 'pdo_mysql',
    'driverOptions' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    ]
];

// Create DBAL connection
$connection = \Doctrine\DBAL\DriverManager::getConnection($conn, $config);
$connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

// Create EntityManager
$entityManager = new EntityManager($connection, $config);

// Reverse-engineer metadata
$driver = new DatabaseDriver($entityManager->getConnection()->getSchemaManager());
$entityManager->getConfiguration()->setMetadataDriverImpl($driver);

$cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
$cmf->setEntityManager($entityManager);
$metadata = $cmf->getAllMetadata();

if (empty($metadata)) {
    die("No metadata found. Check database connection and permissions.");
}

// Generate entities using PHP 8 attributes
$generator = new \Doctrine\ORM\Tools\EntityGenerator();
$generator->setGenerateAnnotations(true); // Required for now (still uses PHPDoc for some parts)
$generator->setGenerateStubMethods(true);
$generator->setRegenerateEntityIfExists(true);
$generator->setUpdateEntityIfExists(true);
$generator->generate($metadata, __DIR__ . '/src/Entity');

echo "✅ Entities successfully generated using modern PHP attributes!\n";
