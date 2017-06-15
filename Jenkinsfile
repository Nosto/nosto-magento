#!/usr/bin/env groovy

node {
    stage "Prepare environment"
        checkout scm
        def environment  = docker.build 'platforms-base'

        environment.inside {
            stage "Update Dependencies"
                sh 'whoami'
                sh "composer install"
        }

    stage "Cleanup"
        deleteDir()
}
