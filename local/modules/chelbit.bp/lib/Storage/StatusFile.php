<?php
namespace ChelBit\BP\Storage;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\SystemException;

class StatusFile extends Base
{
    public function __construct(string $path)
    {
        parent::__construct($path, "w");
    }
    public function save(\ChelBit\BP\Data\Base $data) : void
    {
        file_put_contents($this->path, serialize($data));
    }

    /**
     * @return \ChelBit\BP\Data\Process
     * @throws SystemException
     */

    public function load(): \ChelBit\BP\Data\Base
    {
        $i = 0;
        while ($i<100){
            $i++;
            $content = file_get_contents($this->path);
            $data = unserialize($content);
            if($data instanceof \ChelBit\BP\Data\Base){
                return $data;
            }else{
                sleep(0.2);
            }
        }
        throw new SystemException("ERROR_UNSERIALIZE_DATA");
    }
}