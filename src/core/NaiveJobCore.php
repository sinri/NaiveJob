<?php


namespace sinri\NaiveJob\core;


use Exception;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;

class NaiveJobCore
{
//    /**
//     * @var ArkLogger
//     */
//    protected static $loopLogger=null;
//
//    /**
//     * @return ArkLogger
//     */
//    public static function logger(){
//        if (null===self::$loopLogger) {
//            $path = Ark()->readConfig(['log', 'path']);
//            if ($path !== null) {
//                $logger = new ArkLogger($path, "loop","Ymd",null,true);
//                $level = Ark()->readConfig(['log', 'level'], LogLevel::INFO);
//                $logger->setIgnoreLevel($level);
//            } else {
//                $logger = ArkLogger::makeSilentLogger();
//            }
//            self::$loopLogger=$logger;
//            Ark()->registerLogger("loop",$logger);
//        }
//        return self::$loopLogger;
//    }

    /**
     * @return ArkPDO
     * @throws Exception
     */
    public static function getNewLoopDb(){
        $dbConfigDict = Ark()->readConfig(['pdo', 'loop']);
        $pdoConfig = new ArkPDOConfig($dbConfigDict);
        $pdo = new ArkPDO($pdoConfig);
        $pdo->connect();
        return $pdo;
    }
}