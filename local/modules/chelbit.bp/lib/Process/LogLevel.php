<?php
namespace ChelBit\BP\Process;

enum LogLevel: int
{
    case EMERGENCY = 0;
    case ALERT = 1;
    case CRITICAL = 2;
    case ERROR = 3;
    case WARNING = 4;
    case NOTICE = 5;
    case INFO = 6;
    case DEBUG = 7;
}