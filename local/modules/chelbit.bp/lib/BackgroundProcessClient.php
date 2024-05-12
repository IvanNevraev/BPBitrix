<?php
namespace ChelBit\BP;

use Bitrix\Main\Application;
use Bitrix\Main\Diag\Debug;
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
    private string $serverRoot = "/home/bitrix/www";
    private ?string $moduleName;
    private ?string $methodName;
    private ?string $pidKey;
    private ?int $id;
    private ?string $memoryLimit;
    public function __construct(string $moduleName, string $methodName, string $pidKey)
    {
        sleep(1);
        $data = $this->getByPidKey($pidKey);
        if($data){
            $this->data = $data;
            $this->id = (int)$data["ID"];
        }else{
            $this->serverRoot = Application::getDocumentRoot();
            if(!Loader::includeModule($moduleName)){
                throw new SystemException("Ошибка подключения модуля");
            }
            $this->moduleName = $moduleName;
            $this->methodName = $methodName;
            $this->pidKey = $pidKey;
            $this->data["PID_KEY"] = $pidKey;
            $this->run();
        }
        $this->memoryLimit = ini_get("memory_limit");
        if($this->memoryLimit == "-1"){
            $this->memoryLimit = "не ограничена";
        }
    }
    private function run() : void
    {
        $id = $this->createDbItem();
        $cmd = self::PHP_BIN_PATH." -f ".$this->serverRoot.self::SCHEDULER_PATH." -- '".$this->moduleName."' '".$this->methodName."' '".$id."' > /dev/null 2>/dev/null &";
        exec($cmd);
    }
    private function createDbItem() : int
    {
        $this->setConnection();
        $now = date($this->dateFormat);
        $sql = "INSERT INTO bp (PID_KEY, STATUS, BEGIN_DATE) VALUES ('".$this->pidKey."', 'PROGRESS', '".$now."')";
        if($this->db->exec($sql)){
            $id = $this->db->lastInsertRowID();
            $this->id = $id;
            $this->data["ID"] = $id;
            $this->data["PID_KEY"] = $this->pidKey;
            $this->data["STATUS"] = "PROGRESS";
            $this->data["BEGIN_DATE"] = $now;
            $this->db->close();
            return $id;
        }else{
            throw new SystemException("Ошибка при создании записи в таблице фоновых процессов ".$this->db->lastErrorMsg());
        }
    }
    public function getData(): array
    {
        if($this->loadData($this->id)){
            return $this->data;
        }else{
            throw new SystemException("Ошибка получения данных по фоновому процессу");
        }
    }
    public function getDataForDialog(bool $addMemoryData = true, bool $addTimeProgressData = true) : array
    {
        if($this->loadData($this->id)){
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
        $beginDate = new DateTime($this->data["BEGIN_DATE"], "Y-m-d H-i-s");
        if(strlen($this->data["UPDATE_DATE"]) < 10){
            return "<br> Процесс запущен в ".$beginDate->format("H:i:s");
        }
        $updateDate = new DateTime($this->data["UPDATE_DATE"], "Y-m-d H-i-s");
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
    private function getByPidKey(string $pidKey) : bool|array
    {
        $this->setConnection();
        $sql = "SELECT * FROM bp WHERE PID_KEY='".$pidKey."';";
        $res = $this->db->query($sql);
        if(!$res){
            $this->db->close();
            return false;
        }
        $data = $res->fetchArray(SQLITE3_ASSOC);
        $this->db->close();
        if($data){
            return $data;
        }else{
            return false;
        }
    }
}
