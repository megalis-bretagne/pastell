<?php

class PESViewerTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testGetURL()
    {
        $this->getObjectInstancier()->setInstance('pes_viewer_url', 'https://localhost/');

        $this->mockCurl([
            'https://localhost/bl-xemwebviewer/prepare' =>
                "HTTP/1.1 302 
Date: Wed, 29 Jan 2020 08:23:43 GMT
Server: Apache/2.4.25 (Debian)
Location: https://docker.for.mac.localhost:8443/bl-xemwebviewer/browse;jsessionid=B7537D7BA5DF6F2875F010AE449F5E6E?xwvSession=bb44921a-e267-4912-a571-686a46497b38&docId=pes-aller&etatId=general_view
Content-Language: en-US
Content-Length: 0
Set-Cookie: JSESSIONID=B7537D7BA5DF6F2875F010AE449F5E6E; Max-Age=900; Expires=Wed, 29-Jan-2020 08:38:42 GMT; Path=/bl-xemwebviewer; HttpOnly
"
        ], 302);

        $info = $this->createConnector('pes-viewer', "PES Viewer", 0);
        $this->configureConnector($info['id_ce'], [], 0);

        /** @var PESViewer $pesViewer */
        $pesViewer = $this->getConnecteurFactory()->getConnecteurById($info['id_ce']);
        $result = $pesViewer->getURL("test.pes");

        $this->assertEquals(
            '/bl-xemwebviewer/browse;jsessionid=B7537D7BA5DF6F2875F010AE449F5E6E?xwvSession=bb44921a-e267-4912-a571-686a46497b38&docId=pes-aller&etatId=general_view',
            $result
        );
    }
}
