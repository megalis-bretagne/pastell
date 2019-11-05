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
class NoGuy extends \Codeception\Actor
{
    use _generated\NoGuyActions {
        sendGET as sendGETTrait;
        sendPOST as sendPOSTTrait;
        sendPATCH as sendPATCHTrait;
        sendDELETE as sendDELETETrait;
    }

    public function sendGET($url, $params = array())
    {
        return $this->sendGETTrait($this->getAPIV2URL($url), $params);
    }

    public function sendGETV1($url, $params = array())
    {
        return $this->sendGETTrait($this->getAPIV1URL($url), $params);
    }

    public function sendPOST($url, $params = array())
    {
        return $this->sendPOSTTrait($this->getAPIV2URL($url), $params);
    }

    public function sendPOSTV1($url, $params = array())
    {
        return $this->sendPOSTTrait($this->getAPIV1URL($url), $params);
    }

    public function sendPATCH($url, $params = array(), $files = array())
    {
        return $this->sendPATCHTrait($this->getAPIV2URL($url), $params, $files);
    }

    public function sendDELETE($url, $params = array(), $files = array())
    {
        return $this->sendDELETETrait($this->getAPIV2URL($url), $params, $files);
    }

    private function getAPIV2URL($url)
    {
        return trim(SITE_BASE, "/") . "/api/v2/" . trim($url, "/");
    }

    private function getAPIV1URL($url)
    {
        return trim(SITE_BASE, "/") . "/api/" . trim($url, "/");
    }

    public function amHttpAuthenticatedAsAdmin()
    {
        $I = $this;
        $I->amHttpAuthenticated('admin', 'admin');
    }

    public function verifyJsonResponseOK(
        $expected,
        $http_response_code = \Codeception\Util\HttpCode::OK
    ) {
        $I = $this;
        $I->seeResponseCodeIs($http_response_code);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($expected);
    }
}
