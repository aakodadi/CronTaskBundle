<?php

/*
 * File: Cron.php
 * Project: cron-task
 * Author: Akodadi Abdelhakim <akodadi.abdelhakim@gmail.com>
 * Brief: 
 * 
 * Created on Feb 22, 2016, 2:03:51 PM
 */

namespace Akodakim\CronTaskBundle\Cron;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Description of Cron
 *
 * @author Akodadi Abdelhakim <akodadi.abdelhakim@gmail.com>
 */
class Cron extends \Thread {

    const CRON_PERIODIC = 'PRIODIC';
    const CRON_ONCE = 'ONCE';

    private $type;
    private $sleep_time;
    private $dispatcher;
    private $name;
    private $continue;

    public function __construct(EventDispatcher $dispatcher, $name, $sleep_time, $type = self::CRON_ONCE) {
        $this->sleep_time = $sleep_time;
        $this->dispatcher = $dispatcher;
        $this->name = $name;
        $this->type = $type;
        $this->continue = true;
    }

    public function run() {
        switch ($this->type) {
            case self::CRON_PERIODIC:
                while (true) {
                    $this->synchronized(function() {
                        $this->wait($this->sleep_time * 1e6);
                    });
                    if (!$this->continue) {
                        return;
                    }
                    $this->dispatcher->dispatch($this->name);
                }
                break;
            case self::CRON_ONCE:
                $this->synchronized(function() {
                    $this->wait($this->sleep_time * 1e6);
                });
                if (!$this->continue) {
                    return;
                }
                $this->dispatcher->dispatch($this->name);
                break;
            default :
                throw new Exception('Unknown Cron::$type ' . $this->type);
        }
    }

    public function end() {
        $this->continue = false;
        $this->notify();
    }
    
    function getType() {
        return $this->type;
    }
}
