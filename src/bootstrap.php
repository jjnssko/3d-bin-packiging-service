<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$rootFolder = __DIR__.'/../';

$envNames = ['.env', '.env.local'];
foreach ($envNames as $key => $env) {
    if (false === file_exists($rootFolder . $env)) {
        unset($envNames[$key]);
    }
}

$dotenv = Dotenv::createImmutable($rootFolder, $envNames, false);
$dotenv->load();

$config = ORMSetup::createAttributeMetadataConfiguration([__DIR__], true);
$config->setNamingStrategy(new UnderscoreNamingStrategy());

return EntityManager::create([
    'driver' => 'pdo_mysql',
    'host' => 'mysql',
    'user' => 'root',
    'password' => 'secret',
    'dbname' => 'packing',
], $config);
