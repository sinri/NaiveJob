<?php


namespace sinri\NaiveJob\web\controller;


use Cron\CronExpression;
use Exception;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\web\implement\ArkWebController;
use sinri\NaiveJob\loop\model\NaiveJobParametersModel;
use sinri\NaiveJob\loop\model\NaiveJobQueueModel;
use sinri\NaiveJob\loop\model\NaiveJobScheduleModel;
use sinri\NaiveJob\web\library\TaskLibrary;

class ScheduleController extends ArkWebController
{
    protected $taskLib;
    protected $queueModel;
    protected $parameterModel;
    protected $scheduleModel;
    protected $logger;

    public function __construct()
    {
        parent::__construct();

        $this->taskLib=new TaskLibrary();
        $this->queueModel=new NaiveJobQueueModel(false);
        $this->parameterModel=new NaiveJobParametersModel(false);
        $this->scheduleModel=new NaiveJobScheduleModel(false);
        $this->logger=Ark()->logger('web');
    }

    public function fetchScheduleList(){
        try{
            $status=$this->_readRequest('status',[NaiveJobScheduleModel::STATUS_ON,NaiveJobScheduleModel::STATUS_OFF]);

            $limit=$this->_readRequest("page_size",10,'/^\d+$/');
            $page=$this->_readRequest("page",1,'/^\d+$/');

            $conditions=['status'=>$status];

            $rows=$this->scheduleModel->selectRowsWithSort($conditions,null,$limit,($page-1)*$limit);
            $total=$this->scheduleModel->selectRowsForCount($conditions);

            if(!is_array($rows)){
                throw new Exception("Cannot fetch schedule list");
            }

            $this->_sayOK(['schedules' => $rows, 'total' => $total]);
        }catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 新建一个任务模板
     */
    public function createTaskTemplate(){
        try{
            $taskTitle=$this->_readRequest("task_title",uniqid("Anonymous-Task-"));
            $taskType=$this->_readRequest("task_type");
            $priority=$this->_readRequest("priority");

            $parameters=$this->_readRequest("parameters",[]);

            $taskId=$this->taskLib->createTask($taskType,$taskTitle,$priority,$parameters,true,false);
            $this->_sayOK(['task_id'=>$taskId]);
        }catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 创建一个定时任务计划
     */
    public function createSchedule(){
        try {
            $cronExpression = $this->_readRequest("cron_expression");
            $jobCode = $this->_readRequest("job_code");
            $status = $this->_readRequest("status", NaiveJobScheduleModel::STATUS_OFF);
            $parentTaskId = $this->_readRequest("parent_task_id");

            if (!CronExpression::isValidExpression($cronExpression)) {
                throw new Exception("Cron Invalid");
            }

            $templateTaskRow = $this->queueModel->selectRow(['task_id' => $parentTaskId]);
            if (empty($templateTaskRow)) throw new Exception("Template Task Id Invalid");

            $scheduleId = $this->scheduleModel->insert([
                'cron_expression' => $cronExpression,
                'job_type' => $templateTaskRow['job_type'],
                'job_code' => $jobCode,
                'status' => ($status === NaiveJobScheduleModel::STATUS_ON ? NaiveJobScheduleModel::STATUS_ON : NaiveJobScheduleModel::STATUS_OFF),
                'parent_task_id' => $parentTaskId,
            ]);
            if(!$scheduleId){
                throw new Exception("Cannot create schedule: ".$this->scheduleModel->getPdoLastError());
            }
            $this->_sayOK(['schedule_id'=>$scheduleId]);
        }catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 切换一个计划任务的可执行状态 （ON OFF NEVER）
     */
    public function switchSchedule(){
        try{
            $scheduleId=$this->_readRequest('schedule_id');
            $status=$this->_readRequest('status');
            //$status=($status===NaiveJobScheduleModel::STATUS_ON?NaiveJobScheduleModel::STATUS_ON:NaiveJobScheduleModel::STATUS_OFF);
            $this->scheduleModel->update(['schedule_id'=>$scheduleId],['status'=>$status]);

            $row = $this->scheduleModel->selectRow(['schedule_id' => $scheduleId]);
            if (!$row || $row['status'] !== $status) {
                throw new Exception("Failed to switch schedule");
            }

            $this->_sayOK(['schedule_id' => $scheduleId, 'status' => $status]);
        } catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function searchTemplateTask()
    {
        try {
            $keyword = $this->_readRequest('keyword', '');

            $rows = $this->queueModel->selectRows(
                [
                    ArkSQLCondition::makeConditionsUnion([
                        'task_id' => ArkSQLCondition::makeStringContainsText('task_id', $keyword),
                        'task_title' => ArkSQLCondition::makeStringContainsText('task_title', $keyword),
                    ]),
                    'status' => NaiveJobQueueModel::STATUS_TEMPLATE,
                ],
                10, 0
            );

            $this->_sayOK(['template_task_list' => $rows]);
        } catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}