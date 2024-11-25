<?php
namespace ChelBit\BP\Process;

use Bitrix\Main\Application;

class BackgroundProcessBase
{
    const  PROCESSES_PATH = "/local/modules/chelbit.bp/bp_files/";
    protected string $pidKey;
    protected string $documentRoot;
    protected string $statusPath;
    protected string $logPath;
    protected string $processDir;

    public function __construct(string $pidKey)
    {
        $this->pidKey = $pidKey;
        $this->documentRoot = Application::getDocumentRoot();
        $this->processDir = $this->documentRoot.self::PROCESSES_PATH.$this->pidKey;
        $this->statusPath = $this->documentRoot.self::PROCESSES_PATH.$this->pidKey."/status.txt";
        $this->logPath = $this->documentRoot.self::PROCESSES_PATH.$this->pidKey."/log.txt";
    }
    public function hasProcess() : bool
    {
        return file_exists($this->statusPath);
    }
}
