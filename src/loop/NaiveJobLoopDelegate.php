<?php


namespace sinri\NaiveJob\loop;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\queue\parallel\ParallelQueueDaemonDelegate;
use sinri\NaiveJob\loop\model\NaiveJobControlModel;
use sinri\NaiveJob\loop\model\NaiveJobHeartbeatModel;
use sinri\NaiveJob\loop\model\NaiveJobQueueModel;

class NaiveJobLoopDelegate extends ParallelQueueDaemonDelegate
{
    /**
     * @var ArkLogger
     */
    protected $logger;
    /**
     * @var int
     */
    protected $sleepPeriodInSecond;
    /**
     * @var int
     */
    protected $maxWorkerCount;

    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        $this->logger=Ark()->logger("loop");
        $this->logger->setShowProcessID(true);
        $this->sleepPeriodInSecond=ArkHelper::readTarget($config,['sleep_period'],30);
        $this->maxWorkerCount=ArkHelper::readTarget($config,['worker_count'],5);
    }

    protected function beat($code,$message){
        (new NaiveJobHeartbeatModel())->beat('loop',$code,$message);

    }

    /**
     * @inheritDoc
     */
    public function whenLoopReportError($error)
    {
        $this->logger->error(__METHOD__." Loop Reports Error Now: ".$error);
        $this->beat(__METHOD__,$error);
    }

    /**
     * @inheritDoc
     */
    public function isRunnable()
    {
        $controlModel=(new NaiveJobControlModel());
        $switch=$controlModel->readLastControlValue(NaiveJobControlModel::CONTROL_CODE_QUEUE_SWITCH,NaiveJobControlModel::CONTROL_VALUE_QUEUE_SWITCH_RUN);
        $this->logger->debug(__METHOD__.' Checked Switch Command',['switch'=>$switch]);
        if ($switch===NaiveJobControlModel::CONTROL_VALUE_QUEUE_SWITCH_RUN){
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function shouldTerminate()
    {
        $controlModel=(new NaiveJobControlModel());
        $switch=$controlModel->readLastControlValue(NaiveJobControlModel::CONTROL_CODE_QUEUE_SWITCH,NaiveJobControlModel::CONTROL_VALUE_QUEUE_SWITCH_RUN);
        $this->logger->debug(__METHOD__.' Checked Switch Command',['switch'=>$switch]);
        if ($switch===NaiveJobControlModel::CONTROL_VALUE_QUEUE_SWITCH_STOP){
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function whenLoopShouldNotRun()
    {
        $this->beat(__METHOD__,"Switch as Not Runnable");
        sleep($this->sleepPeriodInSecond);
    }

    /**
     * @inheritDoc
     */
    public function whenNoTaskToDo()
    {
        $this->logger->debug(__METHOD__.' Loop has no task to do, sleep for '.$this->sleepPeriodInSecond. ' seconds');
        $this->beat(__METHOD__,"Loop Free");
        sleep($this->sleepPeriodInSecond);
    }

    /**
     * @inheritDoc
     */
    public function whenTaskNotExecutable($task)
    {
        $this->logger->warning(__METHOD__.' The coming task is not executable');
        $this->beat(__METHOD__,"Task ".$task->getTaskReference());
    }

    /**
     * @inheritDoc
     * Run by Child Process
     */
    public function whenToExecuteTask($task)
    {
        $this->logger->info(__METHOD__.' The coming task is ready to run in child process');
        $this->beat(__METHOD__,"Task ".$task->getTaskReference());
    }

    /**
     * @inheritDoc
     * Run by Child Process
     */
    public function whenTaskExecuted($task)
    {
        $task->afterExecute();

        if (!$task->isReadyToFinish()) {
            $this->logger->warning(__METHOD__.' Task does not want to finish, but we do not care, ha ha ha.',['task_id'=>$task->getTaskReference()]);
        }

        $queue = new NaiveJobQueueModel();
        $afx = $queue->update(
            [
                'task_id' => $task->getTaskReference(),
                'status' => NaiveJobQueueModel::STATUS_RUNNING,
            ],
            [
                'status' => ($task->isDone() ? NaiveJobQueueModel::STATUS_DONE : NaiveJobQueueModel::STATUS_ERROR),
                'finish_time' => NaiveJobQueueModel::now(),
                'feedback' => $task->getExecuteFeedback(),
            ]
        );
        $this->logger->info(__METHOD__ . ' The coming task has been done in child process', ['afx' => $afx]);

        $this->beat(__METHOD__,"Task ".$task->getTaskReference());
    }

    /**
     * @inheritDoc
     * Run by Child Process
     */
    public function whenTaskRaisedException($task, $exception)
    {
        $this->logger->error(__METHOD__.' The coming task met error during working');

        $queue=(new NaiveJobQueueModel());
        $row=$queue->selectRow(['task_id'=>$task->getTaskReference()]);
        $done=$queue->update(
            [
                'task_id'=>$task->getTaskReference(),
                'status'=>NaiveJobQueueModel::STATUS_RUNNING,
            ],
            [
                'status'=>NaiveJobQueueModel::STATUS_ERROR,
                'finish_time'=>NaiveJobQueueModel::now(),
                'feedback'=>$exception->getMessage(),
                'pid'=>-abs($row['pid']),
            ]
        );
        $this->logger->info(__METHOD__.' finish error task',['exception'=>$exception->getMessage(),'update'=>$done]);

        $this->beat(__METHOD__,"Task ".$task->getTaskReference());
    }

    /**
     * @inheritDoc
     * @return NaiveJobBasement|false
     * @throws Exception
     */
    public function checkNextTaskImplement()
    {
        $queue=(new NaiveJobQueueModel());
        $taskRow=$queue->selectRowsWithSort(
            ['status'=>NaiveJobQueueModel::STATUS_ENQUEUED],
            'priority desc, enqueue_time asc',
            1
        );
        if(!$taskRow || count($taskRow)<=0){
            return false;
        }
        $this->logger->info(__METHOD__.' next task found: ',['row'=>$taskRow[0]]);
        return NaiveJobBasement::factory($taskRow[0]);
    }

    /**
     * @inheritDoc
     */
    public function maxChildProcessCountForSinglePooledStyle()
    {
        return $this->maxWorkerCount;
    }

    /**
     * @inheritDoc
     */
    public function whenChildProcessForked($pid, $note = '', $taskReference = null)
    {
        $afx=(new NaiveJobQueueModel())->update(['task_id'=>$taskReference],['pid'=>$pid]);
        $this->logger->info(__METHOD__.' Loop forked a child process',['pid'=>$pid,'task'=>$taskReference,'note'=>$note,'afx'=>$afx]);
        $this->beat(__METHOD__,"PID ".$pid." for Task ".$taskReference);
    }

    /**
     * @inheritDoc
     */
    public function whenChildProcessConfirmedDead($pid, $detail = [])
    {
        $this->logger->warning(__METHOD__.' Loop confirmed a dead child process',['pid'=>$pid,'detail'=>$detail]);
        $queue=new NaiveJobQueueModel();
        $rows=$queue->selectRows(['pid'=>$pid]);
        foreach ($rows as $row){
            if($row['status']==NaiveJobQueueModel::STATUS_RUNNING){
                $afx=$queue->update(
                    [
                        'pid'=>$pid,
                        'status'=>NaiveJobQueueModel::STATUS_RUNNING
                    ],
                    [
                        'pid'=>-abs($pid),
                        'finish_time'=>NaiveJobQueueModel::now(),
                        'status'=>NaiveJobQueueModel::STATUS_ERROR,
                        'feedback'=>"Loop Confirmed Dead",
                    ]
                );
                $this->logger->warning( "Child Process Died while running.",['pid'=>$pid,'afx'=>$afx,'task_id'=>$row['task_id']]);
            }else{
                $afx=$queue->update(
                    [
                        'pid'=>$pid,
                    ],
                    [
                        'pid'=>-abs($pid),
                    ]
                );
                $this->logger->info( "Child Process Death Reconfirmed.",['pid'=>$pid,'afx'=>$afx,'task_id'=>$row['task_id']]);
            }
        }

        $this->beat(__METHOD__,"PID ".$pid);
    }

    /**
     * @inheritDoc
     */
    public function whenPoolIsFull()
    {
        $this->logger->warning(__METHOD__.' Loop has no more worker available now, sleep',['max'=>$this->maxWorkerCount]);
        $this->beat(__METHOD__,"Pool Full Wait");
        sleep($this->sleepPeriodInSecond);
    }

    /**
     * @inheritDoc
     */
    public function beforeFork($task = null)
    {
        if(!$task->beforeExecute()){
            $this->logger->warning("Task declared not executable.",['task_id'=>$task->getTaskReference()]);
            return false;
        }

        $done=(new NaiveJobQueueModel())->update(
            ['status'=>NaiveJobQueueModel::STATUS_ENQUEUED,'task_id'=>$task->getTaskReference()],
            ['status'=>NaiveJobQueueModel::STATUS_RUNNING,'execute_time'=>NaiveJobQueueModel::now()]
        );
        $this->logger->smartLogLite($done,"Loop checking if the task is executable.",['task_id'=>$task->getTaskReference(),'done'=>$done]);
        return ($done===1);

    }

    public function whenLoopTerminates(){
        $this->logger->info(__METHOD__.' Naive Job Loop Terminated');
        $this->beat(__METHOD__,"Terminates");
    }
}