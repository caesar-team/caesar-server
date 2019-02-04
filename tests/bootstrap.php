<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\VarDumper\VarDumper;

$runCommand = function ($command) {
    printf('Running %s %s', $command, PHP_EOL);
    exec(
        sprintf('php %s/../%s', __DIR__, $command),
        $output,
        $code
    );

    if (0 !== $code) {
        VarDumper::dump($output);
        throw new \Exception('Database preparing failed');
    }
};

if ('test' !== $_SERVER['APP_ENV']) {
    throw new \Exception('Only for test environment');
}

(new Dotenv())->load(__DIR__.'/../.env');

echo 'Preparing database...'.PHP_EOL;
$runCommand('bin/console doctrine:schema:drop --full-database --force');
$runCommand('bin/console doctrine:migrations:migrate --no-interaction');
$runCommand('bin/console doctrine:schema:validate');
$runCommand('bin/console doctrine:fixtures:load --no-interaction --append');
