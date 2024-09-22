<?php

namespace Otus\Helpers;

use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\ExceptionHandlerLog;

class CBExceptionLog extends ExceptionHandlerLog
{
    const DEFAULT_LOG_FILE = "local/logs/CBExceptionLog.log";
    private $level;

    /**
     * @param \Throwable $exception
     * @param int $logType
     */
    public function write($exception, $logType)
    {
        //dump([$exception, $logType]);

        //exit();

        //dump($this->logger);
        //$rclass = new \ReflectionClass($this->logger);
        //dump($rclass->getMethods);

        $text = ExceptionHandlerFormatter::format($exception, false, $this->level);

        $context = [
            'type' => static::logTypeToString($logType),
        ];

        $logLevel = static::logTypeToLevel($logType);

        $message = "Otus => {date} - Host: {host} - {type} - {$text}\n";

        dump([$logLevel, $message, $context]);

        $this->logger->log($logLevel, $message, $context);
    }

    public function initialize(array $options)
    {
        echo '<pre>';
        //var_dump($options);
        echo '</pre>';
    }
}