#!/usr/bin/env bash

if [ ! -f /tmp/sonar-scanner-3.3.0.1492-linux/bin/sonar-scanner ]
then
    echo "Installation du client Sonarqube"
    cd /tmp
    wget https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-3.3.0.1492-linux.zip
    unzip sonar-scanner-cli-3.3.0.1492-linux.zip
fi

cd /var/www/pastell/

phpunit --coverage-text --colors=never --coverage-clover coverage-reports/coverage-clover.xml --log-junit coverage-reports/junit.log

/tmp/sonar-scanner-3.3.0.1492-linux/bin/sonar-scanner \
    -Dsonar.projectBaseDir=/var/www/pastell/ \
    -Dsonar.sources=/var/www/pastell/ \
    -Dsonar.host.url=https://sonarqube.libriciel.fr:443 \
    -Dsonar.projectKey=pastell \
    -Dsonar.login=3bb3fc106f2cbe4cdb0088510349ab7d5b1ff3de \
    -Dsonar.exclusions=vendor/**,components/**,ext/**,test/**,temp/**,web/components/**,web/vendor/**,coverage-reports/**,connecteur/seda-ng/xsd/**,web/js/flow.js \
    -Dsonar.php.tests.reportPath=/var/www/pastell/coverage-reports/junit.log \
    -Dsonar.php.coverage.reportPaths=/var/www/pastell/coverage-reports/coverage-clover.xml  \
    -Dsonar.sourceEncoding=UTF-8
