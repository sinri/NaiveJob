<?php


namespace sinri\NaiveJob\web\library;


use Exception;
use sinri\NaiveJob\loop\model\NaiveJobParametersModel;
use sinri\NaiveJob\loop\model\NaiveJobQueueModel;

class TaskLibrary
{
    protected $queueModel;
    protected $parameterModel;
//    protected $logger;

    public function __construct()
    {
        $this->queueModel=new NaiveJobQueueModel(false);
        $this->parameterModel=new NaiveJobParametersModel(false);
//        $this->logger=Ark()->logger('web');
    }

    public function cancelTask($taskId){
        return $this->queueModel->update(
            ['task_id' => $taskId, 'status' => [NaiveJobQueueModel::STATUS_INIT, NaiveJobQueueModel::STATUS_ENQUEUED]],
            ['status' => NaiveJobQueueModel::STATUS_CANCELLED, 'finish_time' => NaiveJobQueueModel::now()]
        );
    }

    public function enqueueTask($taskId){
        return $this->queueModel->update(
            ['task_id' => $taskId, 'status' => [NaiveJobQueueModel::STATUS_INIT, NaiveJobQueueModel::STATUS_CANCELLED, NaiveJobQueueModel::STATUS_ERROR]],
            ['status' => NaiveJobQueueModel::STATUS_ENQUEUED, 'enqueue_time' => NaiveJobQueueModel::now()]
        );
    }

    /**
     * @param int $taskId
     * @param bool $enqueueNow
     * @return mixed
     * @throws Exception
     */
    public function forkTask($taskId,$enqueueNow){
        $templateTaskRow = $this->queueModel->selectRow(['task_id' => $taskId]);
        if (empty($templateTaskRow)) {
            throw new Exception("Invalid Parent Task Id");
        }
        $templateParameterRows = $this->parameterModel->selectRows(['task_id' => $taskId]);

        return $this->queueModel->db()->executeInTransaction(
            function () use ($enqueueNow, $taskId, $templateParameterRows, $templateTaskRow) {
                $forkedTaskId = $this->queueModel->insert([
                    'task_title' => 'Fork-Task-' . $taskId,
                    'task_type' => $templateTaskRow['task_type'],
                    'status' => NaiveJobQueueModel::STATUS_INIT,
                    'priority' => $templateTaskRow['priority'],
                    'apply_time' => NaiveJobQueueModel::now(),
                    'parent_task_id' => $taskId,
                ]);
                if (empty($forkedTaskId)) {
                    throw new Exception("Cannot fork task in queue: ".$this->queueModel->getPdoLastError());
                }

                if (!empty($templateParameterRows)) {
                    $afx=$this->parameterModel->batchRegisterParametersForTask($forkedTaskId,$templateParameterRows);
                }

                if ($enqueueNow) {
                    $afx = $this->queueModel->update(
                        ['task_id' => $forkedTaskId, 'status' => NaiveJobQueueModel::STATUS_INIT],
                        ['status' => NaiveJobQueueModel::STATUS_ENQUEUED, 'enqueue_time' => NaiveJobQueueModel::now()]
                    );
                    if (empty($afx)) {
                        throw new Exception("Cannot enqueue task: ".$this->queueModel->getPdoLastError());
                    }
                }

                //$this->logger->info("Forked Task",['parent_task_id'=>$taskId,'forked_task_id'=>$forkedTaskId,'enqueue_now'=>$enqueueNow]);
                return $forkedTaskId;
            }
        );
    }

    /**
     * @param string $taskType
     * @param string $taskTitle
     * @param int $priority
     * @param array $parameters
     * @param bool $asTemplate
     * @param bool $enqueueNow
     * @return int|false
     * @throws Exception
     */
    public function createTask($taskType, $taskTitle,$priority,$parameters,$asTemplate,$enqueueNow){
        return $this->queueModel->db()->executeInTransaction(function () use ($asTemplate, $enqueueNow, $priority, $taskType, $taskTitle,$parameters) {
            $taskId=$this->queueModel->insert([
                'task_title' => $taskTitle,
                'task_type' => $taskType,
                'status' => ($asTemplate?NaiveJobQueueModel::STATUS_TEMPLATE:NaiveJobQueueModel::STATUS_INIT),
                'priority' => $priority,
                'apply_time' => NaiveJobQueueModel::now(),
                //'parent_task_id' => $taskId,
            ]);

            if (empty($taskId)) {
                throw new Exception("Cannot create task in queue: ".$this->queueModel->getPdoLastError());
            }

            if (!empty($parameters)) {
                $afx=$this->parameterModel->batchRegisterParametersForTask($taskId,$parameters);
            }

            if (!$asTemplate && $enqueueNow) {
                $afx = $this->queueModel->update(
                    ['task_id' => $taskId, 'status' => NaiveJobQueueModel::STATUS_INIT],
                    ['status' => NaiveJobQueueModel::STATUS_ENQUEUED, 'enqueue_time' => NaiveJobQueueModel::now()]
                );
                if (empty($afx)) {
                    throw new Exception("Cannot enqueue task: ".$this->queueModel->getPdoLastError());
                }
            }

            //$this->logger->info("Created Task",['task_id'=>$taskId,'enqueue_now'=>$enqueueNow]);
            return $taskId;
        });
    }
}