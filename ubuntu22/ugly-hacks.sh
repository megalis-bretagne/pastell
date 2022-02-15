#!/bin/bash

# Utilisé le temps qu'on fixe la version de PHPUnit

find /var/www/pastell/test/PHPUnit -type f -exec sed -i 's/protected function setUp()/protected function setUp(): void/g' {} \;
find /var/www/pastell/tests -type f -exec sed -i 's/protected function setUp()/protected function setUp(): void/g' {} \;

find /var/www/pastell/test/PHPUnit -type f -exec sed -i 's/protected function tearDown()/protected function tearDown(): void/g' {} \;
find /var/www/pastell/tests -type f -exec sed -i 's/protected function tearDown()/protected function tearDown(): void/g' {} \;


cp /var/www/pastell/ubuntu22/ObjectInstancier.class.php /var/www/pastell/lib/