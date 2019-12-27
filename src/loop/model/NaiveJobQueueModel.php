<?php


namespace sinri\NaiveJob\loop\model;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\NaiveJob\core\NaiveJobCore;
use sinri\NaiveJob\core\NaiveJobTableModel;

class NaiveJobQueueModel extends NaiveJobTableModel
{

    //INIT ENQUEUED RUNNING DONE ERROR CANCELLED
    const STATUS_INIT="INIT";
    const STATUS_ENQUEUED="ENQUEUED";
    const STATUS_RUNNING="RUNNING";
    const STATUS_DONE="DONE";
    const STATUS_ERROR="ERROR";
    const STATUS_CANCELLED="CANCELLED";
    const STATUS_TEMPLATE="TEMPLATE";

    const PRIORITY_LOW=5;
    const PRIORITY_COMMON=10;
    const PRIORITY_HIGH=15;

    /**
     * @inheritDoc
     */
    public function mappingTableName()
    {
        return "naive_job_queue";
    }


}