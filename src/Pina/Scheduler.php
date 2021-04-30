<?php


namespace Pina;

use GO\Scheduler as GoScheduler;

class Scheduler
{

    /** @var GoScheduler */
    protected $handler;

    public function __construct()
    {
        $this->handler = new GoScheduler();
    }

    /**
     * Запускает планировщик
     */
    public function run()
    {
        $this->handler->run();
    }

    /**
     * Планирует запуск команды по расписанию
     * @param callable $command
     * @param string $timetable
     */
    public function add($command, $timetable = '* * * * *')
    {
        $this->handler->call($command)->at($timetable)->onlyOne();
    }

    /**
     * Планирует запуск команды каждую минуту
     * @param callable $command
     * @param int $minute
     */
    public function everyMinute($command, $minute = 1)
    {
        $minuteExpression = '*';
        if ($minute > 1) {
            $minuteExpression = '*/' . intval($minute);
        }
        $this->add($command, $minuteExpression . ' * * * *');
    }

    /**
     * Планирует запуск команды каждый час
     * @param callable $command
     * @param int $minute
     */
    public function hourly($command, $minute = 0)
    {
        $this->add($command, intval($minute) . ' * * * *');
    }

    /**
     * Планирует запуск команды каждый день
     * @param callable $command
     * @param int $hour
     * @param int $minute
     */
    public function daily($command, $hour = 0, $minute = 0)
    {
        $this->add($command, intval($minute) . ' ' . intval($hour) . ' * * *');
    }

    /**
     * Планирует запуск команды каждую неделю
     * @param callable $command
     * @param int $weekday
     * @param int $hour
     * @param int $minute
     */
    public function weekly($command, $weekday = 0, $hour = 0, $minute = 0)
    {
        $this->add($command, intval($minute) . ' ' . intval($hour) . ' * * ' . intval($weekday));
    }

    /**
     * Планирует запуск команды каждый месяц
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function monthly($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->add($command, intval($minute) . ' ' . intval($hour) . ' ' . intval($day) . ' * *');
    }

    /**
     * Планирует запуск команды в определенный месяц
     * @param callable $command
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function everyMonth($command, $month = 1, $day = 1, $hour = 0, $minute = 0)
    {
        $dayExpression = intval($minute) . ' ' . intval($hour) . ' ' . intval($day);
        $this->add($command, $dayExpression . ' ' . intval($month) . ' *');
    }

    /**
     * Планирует запуск команды по понедельникам
     * @param callable $command
     * @param int $hour
     * @param int $minute
     */
    public function onMonday($command, $hour = 0, $minute = 0)
    {
        $this->weekly($command, 1, $hour, $minute);
    }

    /**
     * Планирует запуск команды по вторникам
     * @param callable $command
     * @param int $hour
     * @param int $minute
     */
    public function onTuesday($command, $hour = 0, $minute = 0)
    {
        $this->weekly($command, 2, $hour, $minute);
    }

    /**
     * Планирует запуск команды по средам
     * @param callable $command
     * @param int $hour
     * @param int $minute
     */
    public function onWednesday($command, $hour = 0, $minute = 0)
    {
        $this->weekly($command, 3, $hour, $minute);
    }

    /**
     * Планирует запуск команды по четвергам
     * @param callable $command
     * @param int $hour
     * @param int $minute
     */
    public function onThursday($command, $hour = 0, $minute = 0)
    {
        $this->weekly($command, 4, $hour, $minute);
    }

    /**
     * Планирует запуск команды по пятницам
     * @param callable $command
     * @param int $hour
     * @param int $minute
     */
    public function onFriday($command, $hour = 0, $minute = 0)
    {
        $this->weekly($command, 5, $hour, $minute);
    }

    /**
     * Планирует запуск команды по субботам
     * @param callable $command
     * @param int $hour
     * @param int $minute
     */
    public function onSaturday($command, $hour = 0, $minute = 0)
    {
        $this->weekly($command, 6, $hour, $minute);
    }

    /**
     * Планирует запуск команды по воскресениям
     * @param callable $command
     * @param int $hour
     * @param int $minute
     */
    public function onSunday($command, $hour = 0, $minute = 0)
    {
        $this->weekly($command, 7, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый январь
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inJanuary($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 1, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый февраль
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inFebruary($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 2, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый март
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inMarch($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 3, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый апрель
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inApril($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 4, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый май
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inMay($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 5, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый июнь
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inJune($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 6, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый июль
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inJuly($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 7, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый август
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inAugust($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 8, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый сентябрь
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inSeptember($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 9, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый октябрь
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inOctober($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 10, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый ноябрь
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inNovember($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 11, $day, $hour, $minute);
    }

    /**
     * Планирует запуск команды каждый декабрь
     * @param callable $command
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function inDecember($command, $day = 1, $hour = 0, $minute = 0)
    {
        $this->everyMonth($command, 12, $day, $hour, $minute);
    }


}