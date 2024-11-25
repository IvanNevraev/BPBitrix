<?php
/**
 * @var \ChelBit\BP\Data\ExecutingParams $executingParams
 */
if (php_sapi_name() !== 'cli') exit('Запуск только из CLI');

use Bitrix\Main\Loader;

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
Loader::includeModule("chelbit.bp");
$executingParams = unserialize(base64_decode($argv[1]));
//ToDo подумать куда и как сообщит о том, что дессериализация неуспешна
Loader::includeModule($executingParams->getModuleName());
if($executingParams->getCallType() === "static"){
    $res = call_user_func($executingParams->getClassName()."::".$executingParams->getMethodName(),$executingParams->getParams());
}else{
    $obj = new $executingParams->getClassName();
    call_user_func([$obj, $executingParams->getMethodName()], $executingParams->getParams());
}
