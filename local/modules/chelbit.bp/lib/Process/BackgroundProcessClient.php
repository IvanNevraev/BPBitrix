<?php
namespace ChelBit\BP\Process;

use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use ChelBit\BP\Data\ExecutingParams;
use ChelBit\BP\Data\Process;
use ChelBit\BP\Storage\StatusFile;

/**
 * Запускает новый процесс и получает параметры
 */
class BackgroundProcessClient extends BackgroundProcessBase
{
    const PHP_BIN_PATH = "/usr/bin/php";
    const SCHEDULER_PATH = "/local/modules/chelbit.bp/tools/scheduler.php";
    private Process $processData;
    public function __construct(
        string $moduleName,
        string $className,
        string $methodName,
        string $pidKey,
        string $callType = "static",
        array $params = []
    )
    {
        sleep(0.5);
        parent::__construct($pidKey);
        if(!$this->hasProcess()){
            $executingParams = new ExecutingParams($moduleName,$className,$methodName, $pidKey, $callType, $params);
            $cmd = self::PHP_BIN_PATH." -f ".$this->documentRoot.self::SCHEDULER_PATH." -- '".base64_encode(serialize($executingParams))."'  > /dev/null 2>/dev/null &";
            exec($cmd);
            $this->createProcess();
        }
        $storage = new StatusFile($this->statusPath);
        $this->processData = $storage->load();
    }
    public function getDataForDialog() : array
    {
        $return = $this->processData->getData();
        $return["SUMMARY"] = $return["STATUS_DESCRIPTION"];
        $return["SUMMARY"] .= "<br> Используется памяти: ".$return["USED_MEMORY"];
        $return["SUMMARY"] .= " Выделено памяти: ".$return["ALLOCATED_MEMORY"];
        $return["SUMMARY"] .= " Органичение памяти: ".$return["LIMIT_MEMORY"];
        $return["SUMMARY"] .= $this->getTimeStatus();
        $return["SUMMARY"] .= $this->getLingToLogFile();
        return $return;

    }
    private function getLingToLogFile() : string
    {
        $path = self::PROCESSES_PATH.$this->pidKey."/log.txt";
        return "<p><a target='_blank' href='$path'>Лог-файл процесса</a></p>";
    }
    private function createProcess() : void
    {
        if(!is_dir($this->processDir)){
            if(!mkdir($this->processDir)){
                throw new SystemException("ERROR_CREATE_FOLDER");
            }
        }
        $processData = new Process();
        $processData->init();
        $storage = new StatusFile($this->statusPath);
        $storage->save($processData);
    }
    private function getTimeStatus() : string
    {
        $beginDate = new DateTime($this->processData->getDataByKey("BEGIN_DATE"), Process::DATE_FORMAT);
//        if(strlen($this->data["UPDATE_DATE"]) < 10){
//            return "<br> Процесс запущен в ".$beginDate->format("H:i:s");
//        }
        $updateDate = new DateTime($this->processData->getDataByKey("UPDATE_DATE"), Process::DATE_FORMAT);
        $timeDiff = $updateDate->getTimestamp()-$beginDate->getTimestamp();
        if((int)$this->processData->getDataByKey("PROCESSED_ITEMS") == 0){
            $calculatedTime = 0;
        }else{
            $calculatedTime = ((int)$this->processData->getDataByKey("TOTAL_ITEMS")-(int)$this->processData->getDataByKey("PROCESSED_ITEMS"))*($timeDiff/(int)$this->processData->getDataByKey("PROCESSED_ITEMS"));
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
