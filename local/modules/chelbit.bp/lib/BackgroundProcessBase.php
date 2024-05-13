<?php
namespace ChelBit\BP;

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;

class BackgroundProcessBase
{
    protected array $data = [
        "PID_KEY" => "---",
        "STATUS" => "PROGRESS", // COMPLETED ERROR
        "BEGIN_DATE" => "", //d-m-Y H:i:s
        "END_DATE" => "", //d-m-Y H:i:s
        "UPDATE_DATE" => "", //d-m-Y H:i:s
        "TOTAL_ITEMS" => 0,
        "PROCESSED_ITEMS" => 0,
        "STATUS_DESCRIPTION" => "",
        "WARNING" => "",
        "USED_MEMORY" => "0Mb",
        "ALLOCATED_MEMORY" => "0Mb"
    ];
    protected string $dateFormat = "d-m-Y H:i:s";
    protected function setData(string $pidKey): void
    {
        $str = $this->dataToStr($this->data);
        $file = new File(Application::getDocumentRoot()."/local/modules/chelbit.bp/bp_files/".$pidKey.".txt");
        $file->open("w+");
        $file->putContents($str);
        $file->close();
    }
    protected function getData($pidKey) : bool
    {
        $file = new File(Application::getDocumentRoot()."/local/modules/chelbit.bp/bp_files/".$pidKey.".txt");
        if($file->isExists()){
            $file->open("r");
            $strData = $file->getContents();
            while (strlen($strData < 5)){
                sleep(0.5);
                $strData = $file->getContents();
            }
            $this->data = $this->strToData((string)$strData);
            $file->close();
            return true;
        }else{
            return false;
        }
    }
    protected function dataToStr(array $data) : string
    {
        $str = $data["PID_KEY"].";".$data["STATUS"].";".$data["BEGIN_DATE"].";".$data["END_DATE"].";".$data["UPDATE_DATE"].";";
        $str .= $data["TOTAL_ITEMS"].";".$data["PROCESSED_ITEMS"].";".$data["STATUS_DESCRIPTION"].";".$data["WARNING"].";";
        $str .= $data["USED_MEMORY"].";".$data["ALLOCATED_MEMORY"];
        return $str;
    }
    protected function strToData($str) : array
    {
        $arr = explode(";",$str);
        return [
            "PID_KEY" => $arr[0],
            "STATUS" => $arr[1], // COMPLETED ERROR
            "BEGIN_DATE" => $arr[2], //Y-m-d H-i-s
            "END_DATE" => $arr[3], //Y-m-d H-i-s
            "UPDATE_DATE" => $arr[4], //Y-m-d H-i-s
            "TOTAL_ITEMS" => $arr[5],
            "PROCESSED_ITEMS" => $arr[6],
            "STATUS_DESCRIPTION" => $arr[7],
            "WARNING" => $arr[8],
            "USED_MEMORY" => $arr[9],
            "ALLOCATED_MEMORY" => $arr[10]
        ];
    }
}
