<?php
namespace ChelBit\BP;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

/**
 * Запускает новый процесс и получает параметры
 */
class BackgroundProcessClient extends BackgroundProcessBase
{
    const PHP_BIN_PATH = "/usr/bin/php";
    const SCHEDULER_PATH = "/local/modules/chelbit.bp/tools/scheduler.php";
    private ?string $pidKey;
    private ?string $memoryLimit;
    public function __construct(string $moduleName, string $methodName, string $pidKey)
    {
        sleep(1);
        if(!$this->getData($pidKey)){
            $serverRoot = Application::getDocumentRoot();
            if(!Loader::includeModule($moduleName)){
                throw new SystemException("Ошибка подключения модуля");
            }
            $this->data["PID_KEY"] = $pidKey;
            $this->data["STATUS"] = "PROGRESS";
            $this->data["BEGIN_DATE"] = date($this->dateFormat);
            $this->setData($pidKey);
            $cmd = self::PHP_BIN_PATH." -f ".$serverRoot.self::SCHEDULER_PATH." -- '".$moduleName."' '".$methodName."' '".$pidKey."' > /dev/null 2>/dev/null &";
            exec($cmd);
        }
        $this->memoryLimit = ini_get("memory_limit");
        if($this->memoryLimit == "-1"){
            $this->memoryLimit = "не ограничена";
        }
        $this->pidKey = $pidKey;
    }
    public function getDataForDialog(bool $addMemoryData = true, bool $addTimeProgressData = true) : array
    {
        if($this->getData($this->pidKey)){
            $return = [];
            $return["STATUS"] = (string)$this->data["STATUS"];
            $return["TOTAL_ITEMS"] = (int)$this->data["TOTAL_ITEMS"];
            $return["PROCESSED_ITEMS"] = (int)$this->data["PROCESSED_ITEMS"];
            $return["WARNING"] = (string)$this->data["WARNING"];
            $return["SUMMARY"] = (string)$this->data["STATUS_DESCRIPTION"];
            if($addMemoryData){
                $return["SUMMARY"] .= "<br> Используется памяти: ".$this->data["USED_MEMORY"];
                $return["SUMMARY"] .= " Выделено памяти: ".$this->data["ALLOCATED_MEMORY"];
                $return["SUMMARY"] .= " Органичение памяти: ".$this->memoryLimit;
            }
            if($addTimeProgressData){
                $return["SUMMARY"] .= $this->getTimeStatus();
            }
            return $return;
        }else{
            throw new SystemException("Ошибка получения данных по фоновому процессу");
        }

    }

    /**
     * @throws ObjectException
     */
    private function getTimeStatus() : string
    {
        $beginDate = new DateTime($this->data["BEGIN_DATE"], $this->dateFormat);
        if(strlen($this->data["UPDATE_DATE"]) < 10){
            return "<br> Процесс запущен в ".$beginDate->format("H:i:s");
        }
        $updateDate = new DateTime($this->data["UPDATE_DATE"], $this->dateFormat);
        $timeDiff = $updateDate->getTimestamp()-$beginDate->getTimestamp();
        if((int)$this->data["PROCESSED_ITEMS"] == 0){
            $calculatedTime = 0;
        }else{
            $calculatedTime = ((int)$this->data["TOTAL_ITEMS"]-(int)$this->data["PROCESSED_ITEMS"])*($timeDiff/(int)$this->data["PROCESSED_ITEMS"]);
        }
        $str = "<br> Процесс запущен в ".$beginDate->format("H:i:s");
        $str .= " Процесс обновлен в ".$updateDate->format("H:i:s");
        $str .= "<br>Прошло ".$this->timeFormat($timeDiff)." ";
        $str .= "Осталось: ".$this->timeFormat((int)$calculatedTime);
        return $str;
    }
    private function timeFormat(int $sek): string
    {
        if($sek > 60){
            $min = ($sek-($sek%60))/60;
            $sek = $sek%60;
            if($min > 60){
                $hour = ($min-($min%60))/60;
                $min = $min%60;
                return $hour." часов ".$min." минут ".$sek." секунд";
            }else{
                return $min." минут ".$sek." секунд";
            }
        }else{
            return $sek." секунд";
        }
    }
}
