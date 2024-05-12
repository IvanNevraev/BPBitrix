<?php
namespace ChelBit\BP\Controller;

use Bitrix\Main\Context;
use ChelBit\BP\BackgroundProcessClient;

class Example extends \Bitrix\Main\Engine\Controller
{
    public function firstAction()
    {
        $PID = Context::getCurrent()->getRequest()->get("PROCESS_TOKEN");
        $bpc = new BackgroundProcessClient("chelbit.bp", "\ChelBit\BP\SchedulerTest::test", $PID);
        return $bpc->getDataForDialog();
    }
}
