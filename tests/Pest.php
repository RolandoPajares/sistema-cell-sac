<?php

use Tests\RefreshSpanishTestDatabase;
use Tests\TestCase;

// Mantiene la suite aislada de la configuración real y de variables heredadas del proceso.
putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('DB_URL=');
putenv('CACHE_STORE=array');
putenv('MAIL_MAILER=array');
putenv('QUEUE_CONNECTION=sync');
putenv('SESSION_DRIVER=array');
putenv('LOG_CHANNEL=stderr');
putenv('LOG_STACK=stderr');
putenv('GEMINI_API_KEY=');
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['DB_URL'] = '';
$_ENV['CACHE_STORE'] = 'array';
$_ENV['MAIL_MAILER'] = 'array';
$_ENV['QUEUE_CONNECTION'] = 'sync';
$_ENV['SESSION_DRIVER'] = 'array';
$_ENV['LOG_CHANNEL'] = 'stderr';
$_ENV['LOG_STACK'] = 'stderr';
$_ENV['GEMINI_API_KEY'] = '';
$_SERVER['APP_ENV'] = 'testing';
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = ':memory:';
$_SERVER['DB_URL'] = '';
$_SERVER['CACHE_STORE'] = 'array';
$_SERVER['MAIL_MAILER'] = 'array';
$_SERVER['QUEUE_CONNECTION'] = 'sync';
$_SERVER['SESSION_DRIVER'] = 'array';
$_SERVER['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_STACK'] = 'stderr';
$_SERVER['GEMINI_API_KEY'] = '';

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshSpanishTestDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
