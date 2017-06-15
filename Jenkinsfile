#!/usr/bin/env groovy

node {
    stage "Prepare environment"
        checkout scm
        def environment  = docker.build 'platforms-base'

        environment.inside {
            stage "Update Dependencies"
                sh "composer install"
                sh "composer dump-autoload --optimize"
                sh "./vendor/bin/pearify process ."

            stage "Phan Analysis"
                sh "./vendor/bin/phan --signature-compatibility --config-file=phan.php"
        }

    stage "Cleanup"
        deleteDir()
}
