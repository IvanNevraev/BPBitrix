<?php
namespace ChelBit\BP;

class SchedulerTest
{
    const TOTAL_ITEMS = 10000;
    public static function test($pid) : void
    {
        $bps = new BackgroundProcessServer($pid);
        $bps->setTotalItems(self::TOTAL_ITEMS);
        $bps->setStatusDescription("Обработка элементов");
        $data = [];
        for($i=0; $i<=self::TOTAL_ITEMS; $i++){
            $data[] = $i;
            $bps->setProcessedItems($i);
            if($i==1254){
                $bps->setWarning("Внимание ошибка при обработке элемента ".$i);
            }
            sleep((rand(1,10)/100));
        }
        $bps->setStatusDescription("Обработка завершена");
        $bps->setCompletedStatus();
    }
}