<?php

class MockHorodateur extends Horodateur
{
    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
    }

    public function getTimestampReply($timestamp_reply)
    {
        return "MOCK TIMESTAMP";
    }

    public function getTimeStamp($timestamp)
    {
        return "1977-02-18 08:40:00";
    }

    public function verify($data, $token)
    {
        return true;
    }
}
