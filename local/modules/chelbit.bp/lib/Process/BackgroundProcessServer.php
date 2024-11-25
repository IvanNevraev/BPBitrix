<?php
namespace ChelBit\BP\Process;

use Bitrix\Main\SystemException;
use ChelBit\BP\Data\Process;
use ChelBit\BP\Storage\Base;
use ChelBit\BP\Storage\StatusFile;

/**
 * Работает в фоновом методе и устаналивает параметры процесса
 */
class BackgroundProcessServer extends BackgroundProcessBase
{
    private Process $processData;
    private Base $storage;
    private Logger $logger;

    /**
     * @throws SystemException
     */
    public function __construct(string $pidKey)
    {
        parent::__construct($pidKey);
        if(!$this->hasProcess()){
            throw new SystemException("PROCESS_NOT_EXIST");
        }
        $storage = new StatusFile($this->statusPath);
        $this->processData = $storage->load();
        $this->storage = $storage;
        $this->logger = new Logger($pidKey);
    }
    /**
     * Сохранение состояние объекта в файл
     */
    public function save() : void
    {
        $this->processData->addData("USED_MEMORY", $this->getUsedMemory());
        $this->processData->addData("ALLOCATED_MEMORY", $this->getUsedMemory(true));
        $this->processData->addData("UPDATE_DATE", date(Process::DATE_FORMAT, time()));
        $this->storage->save($this->processData);
    }

    /**
     * Устанавливает общее количество элементов
     * @params int $totalItems
     */
    public function setTotalItems(int $totalItems): self
    {
        $this->processData->addData("TOTAL_ITEMS", $totalItems);
        $this->save();
        return $this;
    }

    /**
     * Устанавливает количество обработанных элеентов
     * @param int $processedItems
     */
    public function setProcessedItems(int $processedItems): self
    {
        $this->processData->addData("PROCESSED_ITEMS", $processedItems);
        $this->save();
        return $this;
    }

    /**
     * Устанавливает описание статуса, какое действие сейчас выполняет воновый процесс
     * @param string $status
     * @return void
     */
    public function setStatusDescription(string $status): self
    {
        $this->processData->addData("STATUS_DESCRIPTION", $status);
        $this->save();
        return $this;
    }

    /**
     * Устаналивает сообщение некритического предупреждения
     * @param string $warning
     * @param bool $isJoin Добавить к предыдущему сообщению через пробел
     * @return void
     */
    public function setWarning(string $warning, bool $isJoin = false): self
    {
        if($isJoin){
            $this->processData->addData("WARNING", $this->processData->getDataByKey("WARNING")." ".$warning);
        }else {
            $this->processData->addData("WARNING", $warning);
        }
        $this->save();
        return $this;
    }
    /**
     * Устанавливает статус успешного завершения, записывает в поле DATE_END текущее время
     * Аавтомтаически сохраняет данные в файл без учета режима автосохранения
     * Предполагается, что после вызова данного метода с объектом работа окончена
     * @return void
     */
    public function setCompletedStatus(): self
    {
        $this->processData->addData("STATUS", "COMPLETED");
        $this->processData->addData("END_DATE", date(Process::DATE_FORMAT, time()));
        $this->save();
        return $this;
    }

    /**
     *  Устанавливает статус неуспешного завершения, записывает в поле DATE_END текущее время
     *  Аавтомтаически сохраняет данные в файл без учета режима автосохранения
     *  Предполагается, что после вызова данного метода с объектом работа окончена
     * @return void
     */
    public function setErrorStatus(): self
    {
        $this->processData->addData("STATUS", "ERROR");
        $this->processData->addData("END_DATE", date(Process::DATE_FORMAT, time()));
        $this->save();
        return $this;
    }
    public function getLogger() : Logger
    {
        return $this->logger;
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
