<?php


namespace sinri\NaiveJob\schedule;


use Cron\CronExpression;
use Exception;
use sinri\ark\cli\ArkCliProgram;
use sinri\ark\queue\serial\SerialQueueDaemon;
use sinri\NaiveJob\core\CronTabKit;
use sinri\NaiveJob\loop\model\NaiveJobScheduleModel;
use sinri\NaiveJob\schedule\entity\NaiveJobScheduleEntity;

class NaiveJobScheduleDaemon extends ArkCliProgram
{
    protected $scheduleModel;

    public function __construct()
    {
        parent::__construct();
        $this->logger=Ark()->logger("schedule");
        $this->scheduleModel=new NaiveJobScheduleModel();
    }

    /**
     * @return NaiveJobScheduleEntity[]
     */
    protected function fetchScheduleList(){
        $rows=$this->scheduleModel->selectRows(['status'=>NaiveJobScheduleModel::STATUS_ON]);
        $list=[];
        if(empty($rows)||!is_array($rows))return $list;
        foreach ($rows as $row){
            try{
                $list[]=NaiveJobScheduleEntity::makeEntityFromRow($row);
            } catch (Exception $e) {
                $this->logger->warning(__METHOD__.' Ignored Schedule Row Error: '.$e->getMessage());
            }
        }
        return $list;
    }

    /**
     * This should be put in Cron Tab every minute
     */
    public function actionDefault()
    {
        $now=time();
        $this->logger->info(__METHOD__.' checking for '.$now,['parsed'=>date('Y-m-d H:i:s',$now)]);
        // 1. fetch Schedule List
        $scheduleList=$this->fetchScheduleList();
        // 2. check matched
        foreach ($scheduleList as $jobScheduleEntity){
            try {
                $matched=CronTabKit::isMatch($jobScheduleEntity->cronExpression, $now);
                if($matched) {
                    // 3. add them into loop
                    $taskId = $jobScheduleEntity->enqueueJob();
                    $this->logger->info(__METHOD__." Matched!",['task_id'=>$taskId]);
                }
            } catch (Exception $e) {
                $this->logger->warning(__METHOD__." checking schedule failed: ".$e->getMessage(),['schedule'=>$jobScheduleEntity,'now'=>$now]);
            }
        }

    }
}