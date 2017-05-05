<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions {
        amOnPage as amOnPageTrait;
    }

   public function amOnPage(string $page){
       return $this->amOnPageTrait(trim(SITE_BASE,"/")."/".trim($page,"/"));
   }

    public function login(string $name, string $password) {
        $I = $this;
        $I->amOnPage("/");
        $I->fillField('Identifiant',$name);
        $I->fillField('Mot de passe',$password);
        $I->click('Connexion');
        $I->see('Liste des documents');
        $I->dontseeInCurrentUrl("/Connexion/connexion");
    }

    const PHPSESSID = "PHPSESSID";

    static protected $session_cookie = array();

    public function loadSessionSnapshot($key){
        if (empty(self::$session_cookie[$key])){
            return false;
        }
        $this->setCookie(self::PHPSESSID,self::$session_cookie[$key]);
        return true;
    }

    public function saveSessionSnapshot($key){
        self::$session_cookie[$key] = $this->grabCookie(self::PHPSESSID);
    }

    public function amLoggedAsAdmin() {
        $I = $this;
        if ($I->loadSessionSnapshot('admin')) {
            return;
        }
        $I->login("admin","admin");
        $I->amOnPage("/");
        $I->saveSessionSnapshot('admin');
    }

    public function amAnonymous(){
        $I = $this;
        if ($I->loadSessionSnapshot('anonymous')) {
            return;
        }
        $I->amOnPage("/");
        $I->saveSessionSnapshot('anonymous');
    }

}
