<?php

if (php_sapi_name() !== 'cli') exit('Запуск только из CLI');

use Bitrix\Main\Loader;
use ChelBit\Main\Agent\SchedulerAgent;

// Определяем DOCUMENT_ROOT для ядра битрикса
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../../../..');
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

// Не запускать агенты
const NO_AGENT_CHECK = true;

// Запрет сбора статистики
const NO_KEEP_STATISTIC = true;

// Отключаем проверку прав на доступ к файлам и каталогам
const NOT_CHECK_PERMISSIONS = true;

// Запрещаем сброс кеша акселератора
const BX_NO_ACCELERATOR_RESET = true;

// Подключаем ядро
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

const BX_CRONTAB = true;

@set_time_limit(0);
@ignore_user_abort(true);
if($argc < 4){
    exit();
    //ToDo куда то нужно сообщать о том что sheduler вызван не верно
}
if(!Loader::includeModule($argv[1])) {
    exit();
    //ToDo куда то нужно сообщать о том что sheduler вызван не верно
}
Loader::includeModule("chelbit.bp");
call_user_func($argv[2], $argv[3]);
