<?php
namespace ChelBit\BP\Process;

use Bitrix\Main\Diag\Debug;
use ChelBit\BP\Data\Base;
use ChelBit\BP\Data\Log;
use ChelBit\BP\Storage\LogFile;
use Psr\Log\LoggerInterface;

class Logger extends BackgroundProcessBase implements LoggerInterface
{
    private LogFile $logFile;
    private LogLevel $levelForLog = LogLevel::DEBUG; //Уровень от которого и ниже происходи логирование
    private Log $logData;
    private bool $autoSave = true;
    private int $countItemForAutoSave = -1;
    public function __construct(string $pidKey)
    {
        parent::__construct($pidKey);
        $this->logFile = new LogFile($this->logPath);
        $this->logData = new Log();
        $this->logData->addLogItem(LogLevel::INFO, "Start log ...");
        $this->saveLog();
    }
    public function setLogLevel(LogLevel $level) : self
    {
        $this->levelForLog = $level;
        $this->logData->addLogItem(LogLevel::INFO, "SET LOG LEVEL ".$level->name);
        $this->saveLog();
        return $this;
    }
    public function enableAutoSave() : self
    {
        $this->saveMode = true;
        $this->logData->addLogItem(LogLevel::INFO, "ENABLE AUTO SAVE LOG");
        $this->saveLog();
        return $this;
    }
    public function disableAutoSave() : self
    {
        $this->autoSave = false;
        $this->logData->addLogItem(LogLevel::INFO, "DISABLE AUTO SAVE LOG");
        $this->saveLog();
        return $this;
    }

    /**
     * @param int $count Если значение меньшн нуля, сохраняется каждый добавленный эелемент
     * @return $this
     */
    public function setCountAutoSaveItem(int $count) : self
    {
        $this->countItemForAutoSave = $count;
        $this->logData->addLogItem(LogLevel::INFO, "SET_COUNT_AUTO_SAVE_ITEM:".$count);
        $this->saveLog();
        return $this;
    }
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->addToLog(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->addToLog(LogLevel::ALERT, $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->addToLog(LogLevel::CRITICAL, $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->addToLog(LogLevel::ERROR, $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->addToLog(LogLevel::WARNING, $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->addToLog(LogLevel::NOTICE, $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->addToLog(LogLevel::INFO, $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->addToLog(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param LogLevel $level
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function log( $level, \Stringable|string $message, array $context = []): void
    {
        if($level->value <= $this->levelForLog->value){
            switch ($level->value){
                case LogLevel::EMERGENCY->value:
                    $this->emergency($message, $context);
                    break;
                case LogLevel::ALERT->value:
                    $this->alert($message, $context);
                    break;
                case LogLevel::CRITICAL->value:
                    $this->critical($message, $context);
                    break;
                case LogLevel::ERROR->value:
                    $this->error($message, $context);
                    break;
                case LogLevel::WARNING->value:
                    $this->warning($message, $context);
                    break;
                case LogLevel::NOTICE->value:
                    $this->notice($message, $context);
                    break;
                case LogLevel::INFO->value:
                    $this->info($message, $context);
                    break;
                case LogLevel::DEBUG->value:
                    $this->debug($message, $context);
                    break;
            }
        }
    }
    private function addToLog(LogLevel $level, \Stringable|string $message, array $context = []) : void
    {
        $this->logData->addLogItem($level, $message);
        if(count($this->logData->getData()) >= $this->countItemForAutoSave && $this->autoSave){
            $this->saveLog();
        }
    }
    public function saveLog()
    {
        $this->logFile->save($this->logData);
        $this->logData->clear();
    }
}