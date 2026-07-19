<?php

use Carbon\Carbon;

class WorkTimeUtility
{
    protected string $date;
    protected string $timezione = 'Australia/Sydney';

    public function __construct()
    {
        $this->date = Carbon::now()->timezone($this->timezione)->format('Y-m-d');
    }

    public static function getWeekPeriod()
    {

    }

    public static function getFortNightPeriod()
    {

    }

    public function getDatePeriod(Carbon $date)
    {
        $dates = [];
        $i = 0;
        $dayName = $date->format('Y-m-d');
        $weekStartDate = Carbon::parse($date->format('Y-m-d'))->startOf('week')->addDays(2)->format('Y-m-d');

        if (in_array($dayName, ['Monday', 'Tuesday'])) {
            $weekStartDate = Carbon::parse($date->format('Y-m-d'))->startOf('week')->subWeek()->addDays(2)->format('Y-m-d');
        }

        while ($i < 7) {
            $dates[] = Carbon::parse($weekStartDate)->addDays($i)->format('Y-m-d');
            $i++;
        }

        return $dates;
    }
}
