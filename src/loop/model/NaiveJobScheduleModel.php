<?php


namespace sinri\NaiveJob\loop\model;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\NaiveJob\core\NaiveJobCore;
use sinri\NaiveJob\core\NaiveJobTableModel;

class NaiveJobScheduleModel extends NaiveJobTableModel
{
    const STATUS_OFF="OFF";
    const STATUS_ON="ON";
    const STATUS_NEVER="NEVER";

    /**
     * @inheritDoc
     */
    public function mappingTableName()
    {
        return "naive_job_schedule";
    }


}