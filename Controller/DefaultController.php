<?php

namespace Akodakim\CronTaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $sceduler = $this->get(akodakim.cron_task.cron_scheduler);
        $sceduler->addSimpleCron(new \DateTime('+1 day'), 'test'); 
        return $this->render('AkodakimCronTaskBundle:Default:index.html.twig');
    }
}
