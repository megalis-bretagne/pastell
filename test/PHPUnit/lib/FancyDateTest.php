<?php

class FancyDateTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var FancyDate
     */
    private $fancyDate;

    protected function setUp(): void
    {
        setlocale(LC_TIME, "fr_FR.UTF-8");
        $this->fancyDate = new FancyDate();
    }

    public function testGetDateFr()
    {
        $this->assertEquals("26/12/2015 10:21:12", $this->fancyDate->getDateFr("2015-12-26 10:21:12"));
    }

    public function testGetDateFrNow()
    {
        $this->assertMatchesRegularExpression("#^\d{2}/\d{2}/\d{4}\s\d{2}:\d{2}:\d{2}$#", $this->fancyDate->getDateFr());
    }

    public function testIsSameDayNot()
    {
        $this->assertFalse($this->fancyDate->isSameDay("2015-12-25", "2015-12-26"));
    }

    public function testIsSameDay()
    {
        $this->assertTrue($this->fancyDate->isSameDay("2015-12-25 01:12:27", "2015-12-25 02:12:12"));
    }

    public function testIsSameDayFalse()
    {
        $this->assertFalse($this->fancyDate->isSameDay(false, false));
    }

    public function testIsNotSameMonth()
    {
        $this->assertFalse($this->fancyDate->isSameMonth("2015-12-25", "2015-11-26"));
    }

    public function testIsSameMonth()
    {
        $this->assertTrue($this->fancyDate->isSameMonth("2015-12-25", "2015-12-26"));
    }

    public function testIsSameMonthFalse()
    {
        $this->assertFalse($this->fancyDate->isSameMonth("2015-12-25", false));
    }

    public function testIsNotSameYear()
    {
        $this->assertFalse($this->fancyDate->isSameYear("2015-12-25", "2014-12-25"));
    }

    public function testIsSameYear()
    {
        $this->assertTrue($this->fancyDate->isSameYear("2015-12-25", "2015-01-26"));
    }

    public function testIsSameYearFalse()
    {
        $this->assertFalse($this->fancyDate->isSameYear(false, "2014-12-25"));
    }

    public function testGetMoisAnnee()
    {
        $this->assertEquals("Décembre 2014", $this->fancyDate->getMoisAnnee("2014-12-25"));
    }

    public function testGetMoisAnneeSame()
    {
        $this->assertEquals("Décembre", $this->fancyDate->getMoisAnnee(date("Y-12-25")));
    }

    public function testGetDay()
    {
        $this->assertEquals("Aujourd'hui", $this->fancyDate->getDay(date("Y-m-d")));
    }

    public function testGetDayDemain()
    {
        $this->assertEquals("Demain", $this->fancyDate->getDay(date("Y-m-d", strtotime("+1day"))));
    }

    public function testGetDayRandom()
    {
        $this->assertEquals("Mardi 11", $this->fancyDate->getDay("2001-09-11"));
    }

    public function testHasTime()
    {
        $this->assertTrue($this->fancyDate->hasTime("2015-12-26 12:02:23"));
    }

    public function testGetTime()
    {
        $this->assertEquals("12h02", $this->fancyDate->getTime("2015-12-26 12:02:23"));
    }

    public function testGetAllInfo()
    {
        $this->assertMatchesRegularExpression("#dans 2[01] jours#", $this->fancyDate->getAllInfo(date("Y-m-d", strtotime("+20day"))));
    }

    public function testGetFranchDay()
    {
        $this->assertEquals("mardi", $this->fancyDate->getFrenchDay("2001-09-11"));
    }

    public function testGetFranchDate()
    {
        $this->assertEquals("mardi 11 septembre 2001", $this->fancyDate->getFrenchDate("2001-09-11"));
    }

    public function testGet()
    {
        $this->assertEquals("11/09/2001", $this->fancyDate->get("2001-09-11"));
    }

    public function testGetDayATime()
    {
        $this->assertEquals("11/09/2001 à 15:46", $this->fancyDate->getDayATime("2001-09-11 15:46:00"));
    }

    public function testGetMinute()
    {
        $this->assertEquals("02:14 minutes", $this->fancyDate->getMinute(134));
    }

    public function testGetHMS()
    {
        $this->assertEquals("01:26:15", $this->fancyDate->getHMS(5175));
    }

    public function testGetDateSansHeure()
    {
        $this->assertEquals("25/12/2015", $this->fancyDate->getDateSansHeure("2015-12-25 01:12:27"));
    }

    private function assertGetTimeElapsed($expected, $strtotime_args)
    {
        $this->assertEquals(
            $expected,
            $this->fancyDate->getTimeElapsed(date("Y-m-d H:i:s", strtotime($strtotime_args)))
        );
    }

    public function testGetTimeElapsed()
    {
        $this->assertGetTimeElapsed("Dans 10 secondes", "+10 seconds");
    }

    public function testGetTimeElapsed2()
    {
        $this->assertGetTimeElapsed("Il y a 10 secondes", "-10 seconds");
    }

    public function testGetTimeElapsed3()
    {
        $this->assertGetTimeElapsed("Dans 10 minutes", "+10 minutes");
    }

    public function testGetTimeElapsed4()
    {
        $this->assertGetTimeElapsed("Il y a 10 minutes", "-10 minutes");
    }

    public function testGetTimeElapsed5()
    {
        $this->assertGetTimeElapsed("Il y a environ une minute", "-1 minute");
    }

    public function testGetTimeElapsed6()
    {
        $this->assertGetTimeElapsed("Dans 10 heures", "+10 hours");
    }

    public function testGetTimeElapsed7()
    {
        $this->assertGetTimeElapsed("Il y a 10 heures", "-10 hours");
    }

    public function testGetTimeElapsed8()
    {
        $this->assertGetTimeElapsed("Il y a environ une heure", "-1 hours");
    }
}
