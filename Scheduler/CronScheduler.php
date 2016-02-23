<?php

/*
 * File: CronScheduler.php
 * Project: cron-task
 * Author: Akodadi Abdelhakim <akodadi.abdelhakim@gmail.com>
 * Brief: 
 * 
 * Created on Feb 22, 2016, 2:00:57 PM
 */

namespace Akodakim\CronTaskBundle\Scheduler;

use Akodakim\CronTaskBundle\Cron\Cron;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Description of CronScheduler
 *
 * @author Akodadi Abdelhakim <akodadi.abdelhakim@gmail.com>
 */
class CronScheduler {

    /**
     *
     * @var \DateTime[] 
     */
    private $simple_crons_datetime;

    /**
     *
     * @var integer[]
     */
    private $periodic_crons_sleep_time;

    /**
     *
     * @var Cron[] 
     */
    private $crons;

    /**
     *
     * @var EventDispatcher 
     */
    private $dispatcher;

    /**
     *
     * @var boolean
     */
    private $ran;

    /**
     * 
     * @var string
     */
    private $simple_json_file;

    /**
     * 
     * @var string
     */
    private $periodic_json_file;

    public function __construct($simple_json_file, $periodic_json_file) {
        $this->simple_json_file = $simple_json_file;
        $this->periodic_json_file = $periodic_json_file;
        $this->periodic_crons_sleep_time = array();
        $this->simple_crons_datetime = array();
        $this->crons = array();
        $this->dispatcher = new EventDispatcher();
        $this->ran = false;
    }

    public function addPeriodicCron($sleep_time, $name) {
        if ($sleep_time <= 0) {
            return;
        }
        $cron = new Cron($this->dispatcher, $name, $sleep_time, Cron::CRON_PERIODIC);
        $this->crons[$name] = $cron;
        $this->periodic_crons_sleep_time[$name] = $sleep_time;
        $cron->start();

        $handle = fopen($this->periodic_json_file, 'w');
        fwrite($handle, json_decode($this->periodic_crons_sleep_time));
        fclose($handle);
    }

    public function addSimpleCron(\DateTime $datetime, $name) {
        $now = new \DateTime('now');
        $now_timestamp = $now->getTimestamp();
        $date_timestamp = $datetime->getTimestamp();
        $sleep_time = $date_timestamp - $now_timestamp;
        if ($sleep_time <= 0) {
            return;
        }
        $cron = new Cron($this->dispatcher, $name, $sleep_time);
        $this->crons[$name] = $cron;
        $this->simple_crons_datetime[$name] = $datetime;
        $cron->start();
        
        $handle = fopen($this->simple_json_file, 'w');
        fwrite($handle, json_decode($this->simple_crons_datetime));
        fclose($handle);
    }

    public function removeCron($name) {
        $cron = $this->crons[$name];
        $cron->end();
        $cron->join();
        switch ($cron->getType()) {
            case Cron::CRON_ONCE:
                unset($this->simple_crons_datetime[$name]);
                $handle = fopen($this->simple_json_file, 'w');
                fwrite($handle, json_decode($this->simple_crons_datetime));
                fclose($handle);
                break;
            case Cron::CRON_PERIODIC:
                unset($this->periodic_crons_sleep_time[$name]);
                $handle = fopen($this->periodic_json_file, 'w');
                fwrite($handle, json_decode($this->periodic_crons_sleep_time));
                fclose($handle);
                break;
            default :
                throw new Exception('Unknown Cron::$type ' . $this->type);
        }
        unset($this->crons[$name]);
    }

    public function stop() {

        foreach ($this->crons as $cron) {
            $cron->end();
            $cron->join();
        }

        $handle = fopen($this->simple_json_file, 'w');
        fwrite($handle, json_decode($this->simple_crons_datetime));
        fclose($handle);

        $handle = fopen($this->periodic_json_file, 'w');
        fwrite($handle, json_decode($this->periodic_crons_sleep_time));
        fclose($handle);

        $this->ran = false;
    }

    public function run() {
        $this->periodic_crons_sleep_time = array();
        $this->simple_crons_datetime = array();

        if (file_exists($this->periodic_json_file)) {
            $handle = fopen($this->periodic_json_file, 'w+');
            $json = fread($handle, filesize($this->periodic_json_file));
            fclose($handle);
            $this->periodic_crons_sleep_time = json_decode($json);
        }

        if (file_exists($this->simple_json_file)) {
            $handle = fopen($this->simple_json_file, 'w+');
            $json = fread($handle, filesize($this->simple_json_file));
            fclose($handle);
            $this->simple_crons_datetime = json_decode($json);
        }

        foreach ($this->periodic_crons_sleep_time as $name => $sleep_time) {
            $this->addPeriodicCron($sleep_time, $name);
        }

        foreach ($this->simple_crons_datetime as $name => $datetime) {
            $this->addSimpleCron($datetime, $name);
        }

        $this->ran = true;
    }

    function getRan() {
        return $this->ran;
    }

    function setRan($ran) {
        $this->ran = $ran;
        return $this;
    }

}
