<?php

/*
 * File: RunSchedulerCommand.php
 * Project: cron-task
 * Author: Akodadi Abdelhakim <akodadi.abdelhakim@gmail.com>
 * Brief: 
 * 
 * Created on Feb 22, 2016, 12:41:49 PM
 */

namespace Akodakim\CronTaskBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of RunSchedulerCommand
 *
 * @author Akodadi Abdelhakim <akodadi.abdelhakim@gmail.com>
 */
class RunSchedulerCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('crontask:scheduler:run')
                ->setDescription('run cron-task scheduler deamon');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $scheduler = $this->getContainer()->get('akodakim.cron_task.cron_scheduler');
        if ($scheduler->getRan()) {
            $output->writeln("crontask-scheduler is alredy running!");
        } else {
            $scheduler->run();
            if ($scheduler->getRan()) {
                $output->writeln('crontask-scheduler is up and running in the background');
                $output->writeln("\t (use \"crontask:scheduler:stop\" to stop it)");
            } else {
                $output->writeln('crontask-scheduler could not be run!');
            }
        }
        $scheduler->addSimpleCron(new \DateTime('+1 day'), 'test');
    }

}
