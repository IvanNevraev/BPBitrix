<?php
namespace ChelBit\BP\Storage;

use Bitrix\Main\Diag\Debug;
use ChelBit\BP\Data\Process;

class LogFile extends Base
{

    public function __construct(string $path)
    {
        parent::__construct($path, "a+");
    }

    /**
     * @param \ChelBit\BP\Data\Log $data
     * @param bool $saveMode Режим обработки данных, true однострочный, false многострочный
     * @return void
     */
    public function save(\ChelBit\BP\Data\Base $data) : void
    {
        file_put_contents($this->path, (string)$data, FILE_APPEND);
    }

    /**
     * @return \ChelBit\BP\Data\Log
     */
    public function load(): \ChelBit\BP\Data\Base
    {
        //NOT_USED
        return  new \ChelBit\BP\Data\Log();
    }
}