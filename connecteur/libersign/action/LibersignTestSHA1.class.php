<?php

class LibersignTestSHA1 extends ActionExecutor
{

    public const EXPECTED_RESULT = '882216f335750069f0c7911ff46a9f6b698e770d8aeb305c3eec013a99bc8b44';

    public function go()
    {
        /** @var Libersign $my */
        $my = $this->getMyConnecteur();
        $content = file_get_contents(__DIR__ . "/../fixtures/pes.xml");
        $sha1 = $my->getSha1($content);

        if ($sha1 != self::EXPECTED_RESULT) {
            $this->setLastMessage("Empreinte : $sha1 != " . self::EXPECTED_RESULT . " : FAIL !");
            return false;
        }
        $this->setLastMessage("Empreinte : $sha1 : OK");
        return true;
    }
}
