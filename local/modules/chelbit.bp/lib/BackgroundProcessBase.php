<?php
namespace ChelBit\BP;

use Bitrix\Main\Application;
use SQLite3;

class BackgroundProcessBase
{
    protected array $data = [
        "ID" => 0,
        "PID_KEY" => "---",
        "STATUS" => "PROGRESS", // COMPLETED ERROR
        "BEGIN_DATE" => "", //Y-m-d H-i-s
        "END_DATE" => "", //Y-m-d H-i-s
        "UPDATE_DATE" => "", //Y-m-d H-i-s
        "TOTAL_ITEMS" => 0,
        "PROCESSED_ITEMS" => 0,
        "STATUS_DESCRIPTION" => "",
        "WARNING" => "",
        "USED_MEMORY" => "0Mb",
        "ALLOCATED_MEMORY" => "0Mb"
    ];
    protected ?SQLite3 $db;
    protected string $dateFormat = "Y-m-d H-i-s";
    protected function setConnection() : void
    {
        $this->db = new SQLite3(Application::getDocumentRoot()."/local/modules/chelbit.bp/db/bp.db");
    }
    protected function loadData(int $id): bool
    {
        $this->setConnection();
        $sql = "SELECT * FROM bp WHERE ID=".$id.";";
        $res = $this->db->query($sql);
        if(!$res){
            $this->db->close();
            return false;
        }
        $data = $res->fetchArray(SQLITE3_ASSOC);
        $this->db->close();
        if($data){
            $this->data = $data;
            return true;
        }else{
            return false;
        }
    }
}
