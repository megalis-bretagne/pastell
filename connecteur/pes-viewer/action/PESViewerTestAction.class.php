<?php

class PESViewerTestAction extends ActionExecutor
{
    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function go()
    {

        /** @var PESViewer $pesViewer */
        $pesViewer = $this->getMyConnecteur();

        $result = $pesViewer->test();

        //echo $result;
        header_wrapper("Location: $result");
        exit_wrapper();
    }
}
