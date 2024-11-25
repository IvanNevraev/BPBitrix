<?php
namespace ChelBit\BP;

use ChelBit\BP\Process\BackgroundProcessServer;

class SchedulerTest
{
    public static function test($params) : void
    {
        $bps = new BackgroundProcessServer($params["PID_KEY"]);
        $bps->setTotalItems($params["TOTAL_ITEMS"]);
        $bps->setStatusDescription("Обработка элементов");
        $bps->getLogger()->setCountAutoSaveItem(100)->enableAutoSave();
        for($i=0; $i<=$params["TOTAL_ITEMS"]; $i++){
            $bps->setProcessedItems($i);
            if($params["SHOW_WARNING"]){
                $bps->setWarning("Пример вывода ошибки...");
            }
            $bps->getLogger()->log(Process\LogLevel::ERROR, $i." - ".md5(time()));
            sleep((rand(1,10)/100));
        }
        $bps->setStatusDescription("Обработка завершена");
        $bps->setCompletedStatus();
    }
}