<?php
require 'vendor/deployer/deployer/recipe/common.php';

// Define a server for deployment.
// Let's name it "prod".

env('bin/php', function () {
    return "/usr/local/php7.0/bin/php";
});

// Symfony shared dirs
set('shared_dirs', ['var/logs', 'var/sessions', 'web/uploads', 'web/pdf']);
// Symfony writable dirs
set('writable_dirs', ['var/cache', 'var/logs', 'var/sessions', 'web/uploads', 'web/pdf']);
// Symfony shared files
set('shared_files', ['app/config/parameters.yml']);

set('bin_dir', 'bin');
set('var_dir', 'var');

// Assets
set('assets', ['web/css', 'web/js']);
set('dump_assets', true);

// Specify the repository from which to download your project's code.
set('repository', 'git@github.com:GroopyMusic/GroopyMusic.git');

// Environment vars
env('env_vars', 'SYMFONY_ENV=prod');
env('env', 'prod');

/**
* Create cache dir
*/
task('deploy:create_cache_dir', function () {
    // Set cache dir
    env('cache_dir', '{{release_path}}/' . trim(get('var_dir'), '/') . '/cache');

    // Remove cache dir if it exist
    run('if [ -d "{{cache_dir}}" ]; then rm -rf {{cache_dir}}; fi');

    // Create cache dir
    run('mkdir -p {{cache_dir}}');

    // Set rights
    run("chmod -R g+w {{cache_dir}}");
})->desc('Create cache dir');

/**
 * Normalize asset timestamps
 */
task('deploy:assets', function () {
    $assets = implode(' ', array_map(function ($asset) {
        return "{{release_path}}/$asset";
    }, get('assets')));

    $time = date('Ymdhi.s');

    run("find $assets -exec touch -t $time {} ';' &> /dev/null || true");
})->desc('Normalize asset timestamps');


/**
 * Dump all assets to the filesystem
 */
task('deploy:assetic:dump', function () {
    if (!get('dump_assets')) {
        return;
    }

    run('{{bin/php}} {{release_path}}/' . trim(get('bin_dir'), '/') . '/console assetic:dump --env={{env}} --no-debug');
    run('{{bin/php}} {{release_path}}/' . trim(get('bin_dir'), '/') . '/console assets:install --env={{env}} --no-debug {{release_path}}/web');

})->desc('Dump assets');


/**
 * Warm up cache
 */
task('deploy:cache:warmup', function () {

    run('{{bin/php}} {{release_path}}/' . trim(get('bin_dir'), '/') . '/console cache:warmup  --env={{env}} --no-debug');

})->desc('Warm up cache');


/**
 * Migrate database
 */
task('database:migrate', function () {

    run('{{bin/php}} {{release_path}}/' . trim(get('bin_dir'), '/') . '/console doctrine:migrations:migrate --env={{env}} --no-debug --no-interaction');

})->desc('Migrate database');

/**
 * Main task
 */
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:create_cache_dir',
    'deploy:shared',
    'deploy:assets',
    'deploy:vendors',
    'deploy:assetic:dump',
    'deploy:cache:warmup',
    'deploy:writable',
    'deploy:symlink',
    'cleanup',
])->desc('Deploy your project');

after('deploy', 'success');

server('prod', '91.134.248.243')
    ->user('unmutebecg') // Define SSH username
    ->password('tATCU6sbyPE9') // Define SSH user's password
    ->stage('production') // Define stage name
    ->env('deploy_path', '/homez.34/unmutebecg/www')
; // Define the base path to deploy your project to.


