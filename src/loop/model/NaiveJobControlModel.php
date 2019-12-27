<?php


namespace sinri\NaiveJob\loop\model;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\NaiveJob\core\NaiveJobCore;
use sinri\NaiveJob\core\NaiveJobTableModel;

class NaiveJobControlModel extends NaiveJobTableModel
{

    const CONTROL_CODE_QUEUE_SWITCH="QUEUE_SWITCH";

    const CONTROL_VALUE_QUEUE_SWITCH_RUN="RUN"; // see this to run or continue
    const CONTROL_VALUE_QUEUE_SWITCH_SLEEP="SLEEP"; // see this to sleep
    const CONTROL_VALUE_QUEUE_SWITCH_STOP="STOP"; // see this to stop and die

    /**
     * @inheritDoc
     */
    public function mappingTableName()
    {
        return "naive_job_control";
    }

    public function readLastControlValue($code,$default=null,&$controlTime=null){
        $lastCommandRows=$this->selectRowsWithSort(
            [
                'control_code'=>$code,
                'control_time'=>ArkSQLCondition::makeNoGreaterThan('control_time',self::now()),
            ],
            self::standardSortExpression(),
            1
        );
        if(empty($lastCommandRows))return $default;
        $controlTime=$lastCommandRows[0]['control_time'];
        return $lastCommandRows[0]['control_value'];
    }

    /**
     * @param string $code
     * @param string $value
     * @param null|string $control_time [Y-m-d H:i:s]
     * @return bool|string
     */
    public function writeLastControlValue($code,$value,$control_time=null){
        return $this->insert(
            [
                'control_code'=>$code,
                'control_value'=>$value,
                'control_time'=>($control_time===null?$control_time:self::now()),
            ]
        );
    }

    public static function standardSortExpression(){
        return 'control_time desc, control_id desc';
    }
}