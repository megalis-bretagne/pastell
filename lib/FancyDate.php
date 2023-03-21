<?php

class FancyDate
{
    public function getDateFr($date_iso = "")
    {
        if ($date_iso) {
            $time = strtotime($date_iso);
        } else {
            $time = time();
        }
        if (!($time)) {
            return false;
        }
        return date("d/m/Y H:i:s", $time);
    }

    public function isSameDay($date1, $date2)
    {
        if (!$date1 || ! $date2) {
            return false;
        }
        return date('Y-m-d', strtotime($date1)) == date('Y-m-d', strtotime($date2));
    }

    public function isSameMonth($date1, $date2)
    {
        if (!$date1 || ! $date2) {
            return false;
        }
        return date('Y-m', strtotime($date1)) == date('Y-m', strtotime($date2));
    }

    public function isSameYear($date1, $date2)
    {
        if (!$date1 || ! $date2) {
            return false;
        }
        return date('Y', strtotime($date1)) == date('Y', strtotime($date2));
    }

    public function getMoisAnnee($date)
    {
        if ($this->isSameYear($date, date('Y-m-d'))) {
            return ucfirst($this->getFormattedDate($date, "MMMM"));
        } else {
            return ucfirst($this->getFormattedDate($date, "MMMM yyy"));
        }
    }

    public function getDay(string $dateIso = ''): string
    {
        $time = strtotime($dateIso);
        $date = date('Y-m-d', $time);
        $nb_jour = (strtotime($date) - strtotime(date("Y-m-d"))) / 86400;
        if ($nb_jour == 0) {
            return "Aujourd'hui";
        }

        if ($nb_jour == 1) {
            return "Demain";
        }
        return ucfirst($this->getFormattedTime($time, "EEEE d"));
    }

    public function hasTime($date)
    {
        return (date('H:i', strtotime($date)) != '00:00');
    }

    public function getTime($date)
    {
        return $this->getFormattedDate($date, "HH") . "h" . $this->getFormattedDate($date, "mm");
    }

    private function getFormattedDate($date, $format): string
    {
        return $this->getFormattedTime(strtotime($date), $format);
    }

    private function getFormattedTime($time, $format): string
    {
        $currentLocale = setlocale(LC_TIME, '0');
        $formatter = new IntlDateFormatter(
            $currentLocale,
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            TIMEZONE,
            IntlDateFormatter::GREGORIAN,
            $format
        );
        return $formatter->format($time);
    }

    public function getAllInfo($date)
    {
        $result = date('d/m/Y', strtotime($date));

        $nb_jour = ceil((strtotime(date('Y-m-d', strtotime($date))) - strtotime(date("Y-m-d"))) / 86400);

        if ($nb_jour > 1) {
            $result .= " [dans $nb_jour jours]";
        }
        return $result;
    }

    public function getFrenchDay($date_iso)
    {
        //Bug en fonction des locales qui mettent ou pas en majuscule le nom du jour de la semaine
        return lcfirst($this->getFormattedDate($date_iso, "EEEE"));
    }

    public function getFrenchDate($date_iso)
    {
        return lcfirst($this->getFormattedDate($date_iso, "EEEE dd MMMM yyyy")); //"%A %d %B %Y"
    }
    public function get($date_iso)
    {
        return $this->getFormattedDate($date_iso, "dd/MM/yyyy");
    }

    public function getDayATime($date_iso)
    {
        return $this->getFormattedDate($date_iso, "dd/MM/yyyy à HH:mm");
    }

    public function getMinute($second)
    {
        $s = $second % 60;
        $m = intval($second / 60);
        return  sprintf("%02d:%02d minutes", $m, $s);
    }

    public function getHMS($second)
    {
        $s = $second % 60;
        $minute = intval($second / 60);
        $m = $minute % 60;
        $h = intval($minute / 60);
        return sprintf("%02d:%02d:%02d", $h, $m, $s);
    }

    public function getDateSansHeure($date)
    {
        return $this->getFormattedDate($date, "dd/MM/yyyy");
    }

    public function getTimeElapsed($date_iso)
    {
        $time = strtotime($date_iso);
        if (!($time)) {
            return false;
        }

        $now = time();

        $interval = $now - $time;
        if ($interval < 0) {
            $debut = "Dans";
        } else {
            $debut = "Il y a";
        }
        $interval = abs($interval);

        if ($interval < 60) {
            return "$debut $interval secondes";
        }

        $minute = (int) ($interval / 60);

        if ($minute == '1') {
            return "$debut environ une minute";
        }
        if ($minute < 60) {
            return "$debut $minute minutes";
        }

        $heure = (int) ($minute / 60);
        if ($heure == '1') {
            return "$debut environ une heure";
        }

        return "$debut $heure heures";
    }
}
