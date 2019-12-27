<?php


namespace sinri\NaiveJob\schedule\entity;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\NaiveJob\loop\model\NaiveJobParametersModel;
use sinri\NaiveJob\loop\model\NaiveJobQueueModel;

class NaiveJobScheduleEntity
{
    public $scheduleId;
    public $cronExpression;
    public $jobType;
    public $jobCode;
    public $status;
    public $parentTaskId;

    /**
     * @param array $row
     * @return NaiveJobScheduleEntity
     * @throws Exception
     */
    public static function makeEntityFromRow($row){
        if(empty($row)) throw new Exception("Invalid Input Row");
        $entity=new NaiveJobScheduleEntity();
        $entity->scheduleId=ArkHelper::readTarget($row,['schedule_id']);
        $entity->cronExpression=ArkHelper::readTarget($row,['cron_expression']);
        $entity->jobType=ArkHelper::readTarget($row,['job_type']);
        $entity->jobCode=ArkHelper::readTarget($row,['job_code']);
        $entity->status=ArkHelper::readTarget($row,['status']);
        $entity->parentTaskId=ArkHelper::readTarget($row,['parent_task_id']);
        return $entity;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function enqueueJob(){
        $queueModel=new NaiveJobQueueModel(false);
        $parameterModel=new NaiveJobParametersModel(false);

        $templateTaskRow=$queueModel->selectRow(['task_id'=>$this->parentTaskId]);
        if(empty($templateTaskRow))throw new Exception("Invalid Parent Task Id");
        $templateParameterRows=$parameterModel->selectRows(['task_id'=>$this->parentTaskId]);

        return $queueModel->db()->executeInTransaction(function () use ($parameterModel, $queueModel,$templateParameterRows,$templateTaskRow) {
            $taskId=$queueModel->insert([
                'task_title'=>'Schedule-'.$this->scheduleId.'-'.$this->jobType.'-'.$this->jobCode,
                'task_type'=>$templateTaskRow['task_type'],
                'status'=>NaiveJobQueueModel::STATUS_INIT,
                'priority'=>$templateTaskRow['priority'],
                'apply_time'=>NaiveJobQueueModel::now(),
                'parent_task_id'=>$this->parentTaskId,
            ]);
            if(empty($taskId)) throw new Exception("Cannot create task in queue");

            if(!empty($templateParameterRows)){
                $afx=$parameterModel->batchRegisterParametersForTask($taskId,$templateParameterRows);
                //if(empty($afx)) throw new Exception("Cannot register task parameters");
            }

            $afx=$queueModel->update(
                ['task_id'=>$taskId,'status'=>NaiveJobQueueModel::STATUS_INIT],
                ['status'=>NaiveJobQueueModel::STATUS_ENQUEUED,'enqueue_time'=>NaiveJobQueueModel::now()]
            );
            if(empty($afx)) throw new Exception("Cannot enqueue task");

            return $taskId;
        });


    }
}