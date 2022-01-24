<?php

class ActesMessageMetierTest extends PastellTestCase
{
    /**
     * @return Acte
     */
    private function getActe(): Acte
    {
        $acte = new Acte();
        $acte->date = '2019-01-01';
        $acte->numero = '201901010000';
        $acte->codeNature = '6';
        $acte->classification = '1.1 Marchés publics';
        $acte->classificationDate = '2019-04-18';
        $acte->documentPapier = 'N';
        $acte->object = 'Test send TDT 01';
        $acte->acte = '99_AI-999-999100057-20190429-20190429_TEST1-AI-1-1_1.pdf';
        $acte->idActe = '999-999100057-20190429-20190429_TEST1-AI';
        $acte->annexes = [
            '22_CO-999-999100057-20190429-20190429_TEST1-AI-1-1_2.pdf',
            '22_TA-999-999100057-20190429-20190429_TEST1-AI-1-1_3.pdf',
            '22_DD-999-999100057-20190429-20190429_TEST1-AI-1-1_4.pdf',
            '43_AC-999-999100057-20190429-20190429_TEST1-AI-1-1_5.pdf'
        ];
        return $acte;
    }

    /**
     * @throws Exception
     */
    public function testGenerateXmlTransmissionFile()
    {
        $acte = $this->getActe();
        $messageMetier = new ActesMessageMetier($acte);
        $this->assertSame(
            file_get_contents(__DIR__ . '/../fixtures/999-1234----1-1_0.xml'),
            $messageMetier->generateXmlTransmissionFile()
        );
    }

    /**
     * @throws Exception
     */
    public function testGenerateXmlTransmissionFileException()
    {
        $this->expectException(ActesMessageMetierException::class);
        $this->expectExceptionMessage('1.1.1.1.1.1.1.1 Marchés publics');

        $acte = $this->getActe();
        $acte->classification = '1.1.1.1.1.1.1.1 Marchés publics';
        $messageMetier = new ActesMessageMetier($acte);
        $messageMetier->generateXmlTransmissionFile();
    }

    public function testGenerateXmlCancellationFile()
    {
        $messageMetier = new ActesMessageMetier($this->getActe());
        $this->assertSame(
            file_get_contents(__DIR__ . '/../fixtures/999-1234----6-1_0.xml'),
            $messageMetier->generateXmlCancellationFile()
        );
    }
}
