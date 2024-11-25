<?php
namespace ChelBit\BP\Data;

use Bitrix\Main\SystemException;

class Base
{
    protected array $data = [];

    /**
     * Возврачает массив с данными
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Проверяет есть ли данные в хранилище
     * @return bool
     */
    public function hasData() : bool
    {
        return !empty($this->data);
    }

    /**
     * Проверяет есть ли в массиве данные с указанным ключом
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key) : bool
    {
        return array_key_exists($key,$this->data);
    }
    /**
     * Получение данных по ключу
     * @param string $key
     * @return mixed
     * @throws SystemException Если переданный ключ отсуствует в массиве данных
     */
    public function getDataByKey(string $key) : mixed
    {
        $this->checkDataKey($key);
        return $this->data[$key];
    }

    /**
     * Получить тип данных хранящихся в массиве данных по ключу
     * @param string $key
     * @return string
     * @throws SystemException Если переданный ключ отсуствует в массиве данных
     */
    public function getTypeByKey(string $key) : string
    {
        $this->checkDataKey($key);
        return gettype($this->data[$key]);
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param bool $forceType Перезаписать значение если данны с таким ключом существуют
     * @return $this
     * @throws SystemException
     */
    public function addData(string $key, mixed $data, bool $forceType = true) : self
    {
        if(!$forceType && $this->hasKey($key)){
            throw new SystemException("KEY_ALREADY_EXIST");
        }
        $this->data[$key] = $data;
        return $this;
    }

    /**
     * Когда нужно добавить элемент без ключа
     * @param mixed $data
     * @return $this
     */
    public function addItem(mixed $data) : self
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * Получить следующий элемент
     * @return mixed
     */
    public function getNext() : mixed
    {
        return next($this->data);
    }
    public function clear() :self
    {
        $this->data = [];
        return $this;
    }

    /**
     * Проверяет возможность использования ключа для работы с массивом данных
     * @param string $key
     * @return void
     * @throws SystemException
     */
    private function checkDataKey(string $key) : void
    {
        if(!array_key_exists($key, $this->data)){
            throw new SystemException("KEY_NO_EXIST");
        }
    }
}