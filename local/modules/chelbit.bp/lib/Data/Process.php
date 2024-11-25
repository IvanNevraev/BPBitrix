<?php
namespace ChelBit\BP\Data;

use Bitrix\Main\Type\DateTime;

class Process extends Base
{
    const DATE_FORMAT = "d.m.Y H:i:s";
    const BASE_DATA = [
        "STATUS" => "PROGRESS", // COMPLETED ERROR
        "BEGIN_DATE" => "", //d-m-Y H:i:s
        "END_DATE" => "", //d-m-Y H:i:s
        "UPDATE_DATE" => "", //d-m-Y H:i:s
        "TOTAL_ITEMS" => 0,
        "PROCESSED_ITEMS" => 0,
        "STATUS_DESCRIPTION" => "",
        "WARNING" => "",
        "USED_MEMORY" => "0Mb",
        "ALLOCATED_MEMORY" => "0Mb",
        "LIMIT_MEMORY" => "0Mb"
    ];
    public function __construct()
    {
        foreach (self::BASE_DATA as $key => $value){
            $this->addData($key, $value);
        }
    }
    public function init() : self
    {
        $this->addData("BEGIN_DATE", date(self::DATE_FORMAT, time()));
        $memoryLimit = ini_get("memory_limit");
        if($memoryLimit == "-1"){
            $memoryLimit = "нет ограничений";
        }
        $this->addData("LIMIT_MEMORY", $memoryLimit);
        return $this;
    }
    public function getTimeStatus() : string
    {
        return "123";
    }

}