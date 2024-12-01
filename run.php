<?php

use App\Application;
use App\Client\Packing3dBinClient;
use App\Factory\RepositoryFactory;
use App\Service\BoxService;
use App\Service\PackingService;
use App\Service\ProductService;
use App\Service\ResponseHandler;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

/** @var EntityManager $entityManager */
$entityManager = require __DIR__ . '/src/bootstrap.php';

// TODO: Handle exceptions
$repositoryFactory = new RepositoryFactory($entityManager);
$packingClient = new Packing3dBinClient();
$productService = new ProductService();
$boxService = new BoxService(
    $repositoryFactory->getBoxRepository(),
);
$responseHandler = new ResponseHandler();
$packingService = new PackingService(
    $packingClient,
    $repositoryFactory->getPackagingResultRepository(),
);

$request = new Request(
    'POST',
    new Uri('http://localhost/pack'),
    ['Content-Type' => 'application/json'],
    $argv[1]
);

$application = new Application(
    $packingService,
    $productService,
    $boxService,
    $responseHandler,
);
$response = $application->run($request);

echo "<<< In:\n" . Message::toString($request) . "\n\n";
echo ">>> Out:\n" . Message::toString($response) . "\n\n";
