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
       return $this->amOnPageTrait(SITE_BASE."/".$page);
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
}
