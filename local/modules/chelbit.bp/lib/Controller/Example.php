<?php
namespace ChelBit\BP\Controller;

use Bitrix\Main\Context;
use ChelBit\BP\Process\BackgroundProcessClient;

class Example extends \Bitrix\Main\Engine\Controller
{
    public function firstAction()
    {
        $PID = Context::getCurrent()->getRequest()->get("PROCESS_TOKEN");
        $params = [
            "TOTAL_ITEMS" => 100000,
            "SHOW_WARNING" => true,
            "ARRAY" => [1, 2, 3, 4, 5]
        ];
        $bpc = new BackgroundProcessClient("chelbit.bp", "\ChelBit\BP\SchedulerTest", "test", $PID, "static", $params);
        return $bpc->getDataForDialog();
    }
}
