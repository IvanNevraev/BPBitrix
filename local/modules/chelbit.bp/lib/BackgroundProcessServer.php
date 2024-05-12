<?php
namespace ChelBit\BP;

use Bitrix\Main\SystemException;

/**
 * Работает в фоновом методе и устаналивает параметры процесса
 */
class BackgroundProcessServer extends BackgroundProcessBase
{
    private string $saveMode = "auto";
    private ?int $id;
    private int $timer = 0;

    /**
     * @throws SystemException
     */
    public function __construct(int $id)
    {
        $this->timer = time();
        $this->setConnection();
        if(!$this->loadData($id)){
            $this->db->close();
            throw new SystemException("Ошибка получения данных объекта из таблицы");
        }
        $this->id = $id;
        $this->db->close();
    }
    /**
     * Сохранение состояние объекта в таблицу sqlite
     * @throws SystemException В случае ошибки сохранения данных в таблице
     */
    public function save() : void
    {
        $timerDiff = time()-$this->timer;
        if($timerDiff >= 5 || $this->data["STATUS"]=="COMPLETED" || $this->data["STATUS"]=="ERROR"){
            $this->timer = time();
            $this->saveCounter = 0;
            $this->setConnection();
            $this->data["UPDATE_DATE"] = date($this->dateFormat);
            $sql = "UPDATE bp SET STATUS='".$this->data["STATUS"]."', END_DATE='".$this->data["END_DATE"]."', ";
            $sql .= "UPDATE_DATE='".$this->data["UPDATE_DATE"]."', TOTAL_ITEMS=".(int)$this->data["TOTAL_ITEMS"].", ";
            $sql .= "PROCESSED_ITEMS=".(int)$this->data["PROCESSED_ITEMS"].", STATUS_DESCRIPTION='".$this->data["STATUS_DESCRIPTION"]."', ";
            $sql .= "WARNING='".$this->data["WARNING"]."', USED_MEMORY='".$this->getUsedMemory()."', ";
            $sql .= "ALLOCATED_MEMORY='".$this->getUsedMemory(true)."' WHERE ID=".$this->id.";";
            if(!$this->db->exec($sql)){
                $this->db->close();
                throw new SystemException("Ошибка сохранения состояния фонового процесса");
            }
            $this->db->close();
        }
    }

    /**
     * В этом случае метод save вызывается автоматичеси после вызова любого сеттера
     * @return void
     */
    public function enableAutoSaveMode(): void
    {
        $this->saveMode = "auto";
    }

    /**
     * В этом случае необходимо вручную вызыывать метод save после установки параметров data
     * @return void
     */
    public function disableAutoSaveMode(): void
    {
        $this->saveMode = "manual";
    }

    /**
     * Устанавливает общее количество элементов
     * @params int $totalItems
     * @throws SystemException
     */
    public function setTotalItems(int $totalItems): void
    {
        $this->data["TOTAL_ITEMS"] = $totalItems;
        if($this->saveMode == "auto"){
            $this->save();
        }
    }

    /**
     * Устанавливает количество обработанных элеентов
     * @param int $processedItems
     * @throws SystemException
     */
    public function setProcessedItems(int $processedItems): void
    {
        $this->data["PROCESSED_ITEMS"] = $processedItems;
        if($this->saveMode == "auto"){
            $this->save();
        }
    }

    /**
     * Устанавливает описание статуса, какое действие сейчас выполняет воновый процесс
     * @param string $status
     * @return void
     * @throws SystemException
     */
    public function setStatusDescription(string $status): void
    {
        $this->data["STATUS_DESCRIPTION"] = $status;
        if($this->saveMode == "auto"){
            $this->save();
        }
    }

    /**
     * Устаналивает сообщение некритического предупреждения
     * @param string $warning
     * @param bool $isJoin Добавить к предыдущему сообщению через пробел
     * @return void
     * @throws SystemException
     */
    public function setWarning(string $warning, bool $isJoin = false): void
    {
        if($isJoin){
            $this->data["WARNING"] = $this->data["WARNING"]." ".$warning;
        }else{
            $this->data["WARNING"] = $warning;
        }
        if($this->saveMode == "auto"){
            $this->save();
        }
    }
    /**
     * Устанавливает статус успешного завершения, записывает в поле DATE_END текущее время
     * Аавтомтаически сохраняет данные в БД без учета режима автосохранения
     * Предполагается, что после вызова данного метода с объектом работа окончена
     * @throws SystemException
     * @return void
     */
    public function setCompletedStatus(): void
    {
        $this->data["STATUS"] = "COMPLETED";
        $this->data["END_DATE"] = date($this->dateFormat);
        $this->save();
    }

    /**
     *  Устанавливает статус неуспешного завершения, записывает в поле DATE_END текущее время
     *  Аавтомтаически сохраняет данные в БД без учета режима автосохранения
     *  Предполагается, что после вызова данного метода с объектом работа окончена
     * @return void
     * @throws SystemException
     */
    public function setErrorStatus(): void
    {
        $this->data["STATUS"] = "ERROR";
        $this->data["END_DATE"] = date($this->dateFormat);
        $this->save();
    }
    private function getUsedMemory($realUsage = false) : string
    {
        $usedMemory = memory_get_usage($realUsage);
        if($usedMemory > 1000000){
            $usedMemory = round(($usedMemory/1000000), 2);
            $return = $usedMemory."Mb";
        }else{
            $usedMemory = round(($usedMemory/1000),2);
            $return = $usedMemory."Kb";
        }
        return $return;
    }
}
