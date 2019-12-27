<?php


namespace sinri\NaiveJob\web\controller;


use Exception;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\web\implement\ArkWebController;
use sinri\NaiveJob\loop\model\NaiveJobParametersModel;
use sinri\NaiveJob\loop\model\NaiveJobQueueModel;
use sinri\NaiveJob\web\library\TaskLibrary;

class QueueController extends ArkWebController
{
    protected $taskLib;
    protected $queueModel;
    protected $parameterModel;
    protected $logger;

    public function __construct()
    {
        parent::__construct();

        $this->taskLib=new TaskLibrary();
        $this->queueModel=new NaiveJobQueueModel(false);
        $this->parameterModel=new NaiveJobParametersModel(false);
        $this->logger=Ark()->logger('web');
    }

    public function dashboardData(){
        $status_stat_rows=$this->queueModel->selectRowsForFieldsWithSort(
            'status,count(*) as number',
            [], null, 0,0,null,
            ['status']
        );
        $this->_sayOK([
            'status_stat'=>$status_stat_rows,
        ]);
    }

    public function listTasksInQueue(){
        $conditions=[];
        $status=$this->_readRequest('status');
        if($status!==null){
            $conditions['status']=$status;
        }

        $sort="priority desc, enqueue_time asc, apply_time asc";

        $limit=$this->_readRequest("page_size",10,'/^\d+$/');
        $page=$this->_readRequest("page",1,'/^\d+$/');

        $rows=$this->queueModel->selectRowsWithSort($conditions,$sort,$limit,$limit*($page-1));
        $total=$this->queueModel->selectRowsForCount($conditions);

        $this->_sayOK(['tasks'=>$rows,'total'=>$total]);
    }

    /**
     * 取消任务
     * 适用于 INIT 和 ENQUEUED 的任务
     * 取消后，直接更新 finish_time
     */
    public function cancelTask(){
        try {
            $taskId = $this->_readRequest("task_id");

            $done=$this->taskLib->cancelTask($taskId);

            if(!$done){
                $this->logger->error("Failed to update queue table for cancelling task", ['done' => $done, 'task_id' => $taskId]);
                throw new Exception($this->queueModel->getPdoLastError());
            }

            $this->logger->info("Updated queue table for cancelling task", ['done' => $done, 'task_id' => $taskId]);
            $this->_sayOK(['done' => $done]);
        }catch (Exception $exception){
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 将完成初始化的任务或未正常完成的任务加入队列
     * 适用于 INIT CANCELLED ERROR 的任务
     * 入队后将更新 enqueue_time
     */
    public function enqueueTask(){
        try {
            $taskId = $this->_readRequest("task_id");

            $done = $this->taskLib->enqueueTask($taskId);

            if(!$done){
                $this->logger->error("Failed to update queue table for enqueueing task", ['done' => $done, 'task_id' => $taskId]);
                throw new Exception($this->queueModel->getPdoLastError());
            }

            $this->logger->info("Updated queue table for enqueueing task", ['done' => $done, 'task_id' => $taskId]);
            $this->_sayOK(['done' => $done]);
        }catch (Exception $exception){
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 根据某一任务新建一个副本
     * 默认为 INIT ，可选直接做ENQUEUE
     */
    public function forkTask()
    {
        try {
            $taskId = $this->_readRequest("task_id");
            $enqueueNow = $this->_readRequest('enqueue_now', 'NO');

            $forkedTaskId=$this->taskLib->forkTask($taskId,$enqueueNow==='YES');
            $this->_sayOK(['task_id' => $forkedTaskId]);
        } catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 创建一个任务
     * 需要提供名称、类型、优先级，可选是否要立即执行
     */
    public function createTask(){
        try{
            $taskTitle=$this->_readRequest("task_title",uniqid("Anonymous-Task-"));
            $taskType=$this->_readRequest("task_type");
            $priority=$this->_readRequest("priority");

            $enqueueNow = $this->_readRequest('enqueue_now', 'NO');

            $parameters=$this->_readRequest("parameters",[]);

            $taskId=$this->taskLib->createTask($taskType,$taskTitle,$priority,$parameters,false,$enqueueNow==='YES');

            $this->_sayOK(['task_id'=>$taskId]);
        }catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}