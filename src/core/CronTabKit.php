<?php


namespace sinri\NaiveJob\core;


use Cron\CronExpression;
use DateTime;
use Exception;

class CronTabKit
{
    /**
     * @param $cronExpression
     * @param string|int $now
     * @return bool
     * @throws Exception
     */
    public static function isMatch($cronExpression,$now='now'){
        $cron = CronExpression::factory($cronExpression);
        return $cron->isDue(date('Y-m-d H:i',$now));
    }
}