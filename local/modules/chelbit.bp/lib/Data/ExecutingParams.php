<?php

namespace ChelBit\BP\Data;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use http\Params;

class ExecutingParams extends Base
{
    /**
     * @throws SystemException
     */
    public function __construct(
        string $moduleName,
        string $className,
        string $methodName,
        string $pidKey,
        string $callType = "static",
        array $params = []
    )
    {
        $this->checkModuleName($moduleName);
        $this->checkClassName($className);
        $object = new $className;
        $this->checkMethodName($object, $methodName);
        $params["PID_KEY"] = $pidKey;
        $this->addData("moduleName", $moduleName)->addData("className", $className)->addData("methodName", $methodName);
        $this->addData("pidKey", $pidKey)->addData("callType", $callType)->addData("params", $params);
    }
    public function getModuleName() : string
    {
        return $this->getDataByKey("moduleName");
    }
    public function getClassName() : string
    {
        return $this->getDataByKey("className");
    }
    public function getMethodName() : string
    {
        return $this->getDataByKey("methodName");
    }
    public function getPidKey() : string
    {
        return $this->getDataByKey("pidKey");
    }
    public function getParams() : array
    {
        return $this->getDataByKey("params");
    }
    public function getCallType() : string
    {
        return $this->getDataByKey("callType");
    }
    private function checkModuleName(string $moduleName) : void
    {
        if(!Loader::includeModule($moduleName)){
            throw new SystemException("ERROR_LOADING_MODULE");
        }
    }

    private function checkClassName(string $className) : void
    {
        if(!class_exists($className)){
            throw new SystemException("CLASS_NOT_EXIST");
        }
    }
    private function checkMethodName(object $object, string $methodName) : void
    {
        if(!method_exists($object,$methodName)){
            throw new SystemException("METHOD_NOT_EXIST");
        }
    }

}