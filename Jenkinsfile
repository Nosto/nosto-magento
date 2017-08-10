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

            stage "Code Sniffer"
                catchError {
                    sh "./vendor/bin/phpcbf --standard=ruleset.xml app || true"
                    sh "./vendor/bin/phpcs --standard=ruleset.xml --severity=10 --report=checkstyle --report-file=phpcs.xml app lib || true"
                }
                step([$class: 'hudson.plugins.checkstyle.CheckStylePublisher', pattern: 'phpcs.xml', unstableTotalAll:'0'])

            stage "Copy-Paste Detection"
                sh "./vendor/bin/phpcpd --exclude=vendor --exclude=build --log-pmd=phpcpd.xml app || true"

            stage "Mess Detection"
                catchError {
                    sh "./vendor/bin/phpmd . xml codesize,naming,unusedcode,controversial,design --exclude vendor,var,build,tests --reportfile phpmd.xml || true"
                }
                //step([$class: 'PmdPublisher', pattern: 'phpmd.xml', unstableTotalAll:'0'])

            stage "Phan Analysis"
                catchError {
                    sh "./vendor/bin/phan --config-file=phan.php --output-mode=checkstyle --output=phan.xml || true"
                }
                step([$class: 'hudson.plugins.checkstyle.CheckStylePublisher', pattern: 'phan.xml', unstableTotalAll:'0'])

            stage "Package"
                version = sh(returnStdout: true, script: 'git rev-parse --short HEAD').trim()
                sh "./vendor/bin/magazine package magazine.json ${version} -v"
                sh 'chmod 755 *.tgz' 
                archiveArtifacts "Nosto_Tagging-${version}.tgz"
        }

    stage "Cleanup"
        deleteDir()
}
