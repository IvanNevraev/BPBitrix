<?php
namespace ChelBit\BP\Data;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\SystemException;
use ChelBit\BP\Process\LogLevel;

class Log extends Base
{
    public function addLogItem(LogLevel $level, string $message) : self
    {
        parent::addItem(
            [
                "DATE" => date(Process::DATE_FORMAT, time()),
                "LEVEL" => $level->name,
                "MESSAGE" => $message
            ]
        );
        return $this;
    }
    public function __toString(): string
    {
        $return = "";
        if(count($this->data) <=1 ){
            $return .= "[".implode("] [", reset($this->data))."] ".PHP_EOL;
        }
        reset($this->data);
        while ($data = next($this->data)){
            $return .= "[".implode("] [", $data)."] ".PHP_EOL;
        }
        return $return;
    }
    public function addData(string $key, mixed $data, bool $forceType = true) : never
    {
        throw new SystemException("NOT_USE_THIS_METHOD");
    }
    public function addItem(mixed $data) : never
    {
        throw new SystemException("NOT_USE_THIS_METHOD");
    }
}