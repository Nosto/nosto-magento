#!/usr/bin/env groovy

pipeline {
  agent none

  stages {
    stage('Prepare environment') {
      agent { dockerfile true }
      steps {
        checkout scm
      }
    }
    stage('Prepare PhpStorm environment') {
      agent { docker { image 'nosto/phpstorm:2018.2-eap' } }
      steps {
        checkout scm
      }
    }
    stage('Update Dependencies') {
      agent { dockerfile true }
      steps {
        sh "composer install --no-progress --no-suggest"
        sh "composer dump-autoload --optimize"
        sh "./vendor/bin/pearify process ."
      }
    }

    stage('PhpStorm Inspections') {
      agent { docker { image 'nosto/phpstorm:2018.2-eap' } }
      steps {
        script {
          sh "/home/plugins/PhpStorm-182.3684.37/bin/inspect.sh || true" /* Initializes the IDE and the user preferences directory */
          sh "./vendor/bin/phpstorm-inspect /home/plugins/PhpStorm-182.3684.37/bin/inspect.sh ~/.PhpStorm2018.2/system . .idea/inspectionProfiles/Project_Default.xml ./app checkstyle > chkintellij.xml"
        }
      }
    }

    stage('Code Sniffer') {
      agent { dockerfile true }
      steps {
        catchError {
          sh "./vendor/bin/phpcs --standard=ruleset.xml --severity=3 --report=checkstyle --report-file=chkphpcs.xml app || true"
        }
      }
    }

    stage('Copy-Paste Detection') {
      agent { dockerfile true }
      steps {
        catchError {
          sh "./vendor/bin/phpcpd --exclude=vendor --exclude=build --log-pmd=phdpcpd.xml app || true"
        }
      }
    }

    stage('Mess Detection') {
      agent { dockerfile true }
      steps {
        catchError {
          sh "./vendor/bin/phpmd . xml codesize,naming,unusedcode,controversial,design --exclude vendor,var,build,tests --reportfile pmdphpmd.xml || true"
        }
      }
    }

    stage('Package') {
      agent { dockerfile true }
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
      agent { dockerfile true }
      steps {
        catchError {
          sh "./vendor/bin/phan --config-file=phan.php --output-mode=checkstyle --output=chkphan.xml"
        }
      }
    }
  }

  post {
    always {
      node('master') {
        checkstyle pattern: 'chk*.xml', unstableTotalAll:'0'
        pmd pattern: 'pmd*.xml', unstableTotalAll:'0'
        deleteDir()
      }
    }
  }
}
