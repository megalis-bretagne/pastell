<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that frontpage works');
//$I->amOnPage('/');
$I->amOnUrl(SITE_BASE);
$I->see('Pastell');