<?php

namespace Deployer;

require 'recipe/symfony4.php';

// Application settings
set('application', 'caesar');                   // Set application name
set('repository', 'git@bitbucket.org:4xxi/caesarapp-server.git');                              // Set repository
inventory('config/deployer/hosts.yaml');            // Set deploy targets in this file

// Basic settings
set('default_stage', 'dev');
set('ssh_multiplexing', true);
set('allow_anonymous_stats', false);
set('git_tty', true);

// Shared and writable files/dirs between deploys
add('shared_files', ['config/packages/prod/parameters.yaml']);
set('shared_dirs', ['var/log', 'var/sessions', 'var/jwt', 'public/static']);
set('writable_dirs', ['var/cache', 'var/log', 'var/sessions']);

task('php-fpm:restart', function () {
    run('sudo service php7.2-fpm restart');
});

// Additional pre and post deploy jobs
before('deploy:symlink', 'database:migrate');
after('deploy:failed', 'deploy:unlock');
after('deploy:symlink', 'php-fpm:restart');
