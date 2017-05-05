<?php

class PastellCest {

    const PHPSESSID = "PHPSESSID";

    private $session_cookie;

    public function _before(AcceptanceTester $I) {
        if (! $this->session_cookie){
            $I->login("admin","admin");
            $this->session_cookie = $I->grabCookie(self::PHPSESSID);
        }
        $I->setCookie(self::PHPSESSID,$this->session_cookie);
    }

    public function _after(AcceptanceTester $I) {
    }

}