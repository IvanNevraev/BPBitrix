<?php
namespace ChelBit\BP;

use Bitrix\Main\SystemException;

/**
 * Работает в фоновом методе и устаналивает параметры процесса
 */
class BackgroundProcessServer extends BackgroundProcessBase
{
    private string $saveMode = "auto";
    private ?string $pidKey;

    /**
     * @throws SystemException
     */
    public function __construct(string $pidKey)
    {
        if(!$this->getData($pidKey)){
            throw new SystemException("Ошибка получения данных по процессу");
        }
        $this->pidKey = $pidKey;
    }
    /**
     * Сохранение состояние объекта в файл
     */
    public function save() : void
    {
        $this->data["USED_MEMORY"] = $this->getUsedMemory();
        $this->data["ALLOCATED_MEMORY"] = $this->getUsedMemory(true);
        $this->data["UPDATE_DATE"] = date($this->dateFormat);
        $this->setData($this->pidKey);
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
     * Аавтомтаически сохраняет данные в файл без учета режима автосохранения
     * Предполагается, что после вызова данного метода с объектом работа окончена
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
     *  Аавтомтаически сохраняет данные в файл без учета режима автосохранения
     *  Предполагается, что после вызова данного метода с объектом работа окончена
     * @return void
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
