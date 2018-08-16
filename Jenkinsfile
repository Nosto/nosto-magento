#!/usr/bin/env groovy

pipeline {

  agent { dockerfile true }

  stages {
    stage('Prepare environment') {
      steps {
        checkout scm
      }
    }

    stage('Update Dependencies') {
      steps {
        sh "composer install --no-progress --no-suggest"
        sh "composer dump-autoload --optimize"
        sh "./vendor/bin/pearify process ."
      }
    }

    stage('Code Sniffer') {
      steps {
        catchError {
          sh "./vendor/bin/phpcs --standard=ruleset.xml --severity=3 --report=checkstyle --report-file=chkphpcs.xml app || true"
        }
      }
    }

    stage('Copy-Paste Detection') {
      steps {
        catchError {
          sh "./vendor/bin/phpcpd --exclude=vendor --exclude=build --log-pmd=phdpcpd.xml app || true"
        }
      }
    }

    stage('Mess Detection') {
      steps {
        catchError {
          sh "./vendor/bin/phpmd . xml codesize,naming,unusedcode,controversial,design --exclude vendor,var,build,tests --reportfile pmdphpmd.xml || true"
        }
      }
    }

    stage('Package') {
      steps {
        script {
          version = sh(returnStdout: true, script: 'xmllint --xpath "//config/modules/Nosto_Tagging/version/text()" ./app/code/community/Nosto/Tagging/etc/config.xml').trim()
          sh "./vendor/bin/magazine package magazine.json ${version} -v"
          sh 'chmod 644 *.tgz'
        }
        archiveArtifacts "Nosto_Tagging-${version}.tgz"
      }
    }

    stage('Phan Analysis') {
      steps {
        catchError {
          sh "./vendor/bin/phan --config-file=phan.php --output-mode=checkstyle --output=chkphan.xml"
        }
      }
    }
  }

  post {
    always {
      checkstyle pattern: 'chk*.xml', unstableTotalAll:'0'
      pmd pattern: 'pmd*.xml', unstableTotalAll:'0'
      deleteDir()
    }
  }
}
