<?php
namespace ChelBit\BP\Storage;

use Bitrix\Main\SystemException;

abstract class Base
{
    protected string $path;
    abstract public function save(\ChelBit\BP\Data\Base $data);
    abstract public function load() : \ChelBit\BP\Data\Base;

    /**
     * Пробуем открыть файл и сохраняем путь к нему если все хорошо
     * @param string $path
     * @param string $mode
     * @throws SystemException
     */
    public function __construct(string $path, string $mode)
    {
        if(!file_exists($path)){
            $file = fopen($path, $mode);
            if(!$file){
                throw new SystemException("ERROR_CREATE_FILE");
            }
            fclose($file);
        }
        $this->path = $path;
    }
}