<?php
declare(strict_types=1);

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Entity\Base;
use ChelBit\Base\Constants;

class Chelbit_Bp extends CModule
{

    public $MODULE_ID = 'chelbit.bp';
    public $MODULE_VERSION = "1.0.0";
    public $MODULE_VERSION_DATE = "2024-05-10 23:33:00";
    public $MODULE_NAME = "Фоновые процессы";
    public $MODULE_DESCRIPTION = "Модуль предназначени для запуска и отслеживания статуса фоновых процессов с использованием диалога пошагового процесса";
    public $PARTNER_URI = "https://chelyabinsk.1cbit.ru/";
    public $PARTNER_NAME = "ПервыйБит-Челябинск";

    function __construct()
    {

    }

    function DoInstall(): bool
    {
        if(!extension_loaded("sqlite3")){
            $GLOBALS['APPLICATION']->ThrowException("Для работы модуля необходимо расширение sqlite3");
            return false;
        }
        ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }

    function DoUninstall(): bool
    {

        UnRegisterModule($this->MODULE_ID);
        return true;
    }

    /**
     * @return bool
     */
    function InstallDB(): bool
    {
        return true;

    }

    /**
     * @return bool
     */
    function UnInstallDB(): bool
    {
        return true;
    }
}

