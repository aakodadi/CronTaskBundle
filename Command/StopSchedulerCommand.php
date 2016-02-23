<?php

/*
 * File: StopSchedulerCommand.php
 * Project: cron-task
 * Author: Akodadi Abdelhakim <akodadi.abdelhakim@gmail.com>
 * Brief: 
 * 
 * Created on Feb 22, 2016, 12:50:32 PM
 */

namespace Akodakim\CronTaskBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of StopSchedulerCommand
 *
 * @author Akodadi Abdelhakim <akodadi.abdelhakim@gmail.com>
 */
class StopSchedulerCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('crontask:scheduler:stop')
                ->setDescription('run cron-task scheduler deamon');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $scheduler = $this->getContainer()->get('akodakim.cron_task.cron_scheduler');
        if ($scheduler->getRan()) {
            $scheduler->stop();
            if ($scheduler->getRan()) {
                $output->writeln('crontask-scheduler could not be stoped!');
            } else {
                $output->writeln('crontask-scheduler is stoped');
                $output->writeln("\t (use \"crontask:scheduler:run\" to run it again)");
            }
        } else {
            $output->writeln("crontask-scheduler wasn't running!");
        }
    }

}
