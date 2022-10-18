<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/npm.php';
require 'recipe/deploy/writable.php';
require "deploy_tasks/index.php";

set('bin/php', function () {
    return '/usr/local/bin/php'; // change
});

set('application', 'Laravel CICD');
set('repository', 'git@github.com:yogameleniawan/laravel-cicd-deployer.git'); // Git Repository

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);
set('git_ssh_command', 'ssh -o StrictHostKeyChecking=no');
// use this config, if you deploy on windows
// set('git_tty', false);
// set('ssh_multiplexing', false);

set('keep_releases', 5);

set('writable_mode', 'chmod'); // shared hosting

// Shared files/dirs between deploys
add('shared_files', ['.env']);
add('shared_dirs', ['storage']);

// Writable dirs by web server
add('writable_dirs', [
    "bootstrap/cache",
    "storage",
    "storage/app",
    "storage/framework",
    "storage/logs",
]);

set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');

// Hosts

host('DeployServer') // Name of the server
->setHostname('151.106.119.33') // Hostname or IP address
->set('remote_user', 'u1318812') // SSH user
->set('port', 65002)
->set('branch', 'master')
->set('deploy_path', '~/public_html/deploy');

// Tasks

task('deploy:secrets', function () {
    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
    upload('.env', get('deploy_path') . '/shared');
});

desc('Build assets');
task('deploy:build', [
    'npm:install',
]);

task('deploy', [
    'deploy:prepare',
    'deploy:secrets',       // Deploy secrets
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
    'deploy:shared',
    'artisan:storage:link',
    'artisan:queue:restart',
    'deploy:publish',
    'deploy:unlock',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

// before('deploy:symlink', 'artisan:migrate');
