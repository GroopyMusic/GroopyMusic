<?php
require 'vendor/deployer/deployer/recipe/symfony.php';

// Define a server for deployment.
// Let's name it "prod".
server('prod', '91.134.248.243')
    ->user('unmutebecg') // Define SSH username
    ->password('tATCU6sbyPE9') // Define SSH user's password
    ->stage('production') // Define stage name
    ->env('deploy_path', '/homez.34/unmutebecg/www'); // Define the base path to deploy your project to.

// Specify the repository from which to download your project's code.
set('repository', 'git@github.com:GroopyMusic/GroopyMusic.git');