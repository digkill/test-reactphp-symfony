<?php

ini_set('memory_limit', '1G');
set_time_limit(0);

use App\Drift\DBAL\Connection;
use App\Drift\DBAL\Credentials;
use App\Drift\DBAL\Driver\PostgreSQL\PostgreSQLDriver;
use App\Drift\DBAL\Result;
use App\Kernel;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Nyholm\Psr7\Factory\Psr17Factory;

require __DIR__ . '/../vendor/autoload.php';

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }
    (new Dotenv())->load(__DIR__ . '/../.env');
}

$env = $_SERVER['APP_ENV'] ?? 'prod';
$debug = (bool)($_SERVER['APP_DEBUG'] ?? ('prod' !== $env));

if ($debug) {
    umask(0000);
    Debug::enable();
}


$kernel = new Kernel($env, $debug);

$httpFoundationFactory = new HttpFoundationFactory();
$psr7Factory = new Psr17Factory();
$psrHttpFactory = new PsrHttpFactory($psr7Factory, $psr7Factory, $psr7Factory, $psr7Factory);


$loop = React\EventLoop\Factory::create();

$psPlatform = new PostgreSqlPlatform();
$psDriver = new PostgreSQLDriver($loop);
$credentials = new Credentials(
    getenv('PGSQL_HOST'),
    getenv('PGSQL_PORT'),
    getenv('PGSQL_USER'),
    getenv('PGSQL_PASSWORD'),
    getenv('PGSQL_DATABASE')
);

$connection = Connection::createConnected(
    $psDriver,
    $credentials,
    $psPlatform
);

$callback = function (Psr\Http\Message\ServerRequestInterface $request) use ($kernel, $httpFoundationFactory, $psrHttpFactory, $connection) {
    try {

        $kernel->incrementCount();
        $symfonyRequest = $httpFoundationFactory->createRequest($request);
        $symfonyRequest->attributes->set('count', $kernel->getCount());

        $title = $symfonyRequest->request->get('title');
        $body = $symfonyRequest->request->get('body');
        $data = ['title' => $title, 'body' => $body];

        $promise = $connection
            ->insert('message', $data)
            ->then(function (Result $result) {
                $numberOfRows = $result->fetchCount();
                $firstRow = $result->fetchFirstRow();
                $allRows = $result->fetchAllRows();
            });

        $response = $kernel->handle($symfonyRequest);
    } catch (\Throwable $e) {
        var_dump($e->getMessage());
        return new React\Http\Response(
            500,
            ['Content-Type' => 'text/plain'],
            $e->getMessage()
        );
    }

    return $psrHttpFactory->createResponse($response);
};

$server = new React\Http\Server($callback);

$socket = new React\Socket\Server('0.0.0.0:8081', $loop);
$server->listen($socket);

echo "System Online http://127.0.0.1:8081\n";

$loop->run();