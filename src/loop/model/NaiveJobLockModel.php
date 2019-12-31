<?php


namespace sinri\NaiveJob\loop\model;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\NaiveJob\core\NaiveJobTableModel;

class NaiveJobLockModel extends NaiveJobTableModel
{

    /**
     * @inheritDoc
     */
    public function mappingTableName()
    {
        return "naive_job_lock";
    }

    /**
     * @param $taskId
     * @param $locks
     * @return bool|string
     * @throws Exception
     */
    public function batchRegisterLocksForTask($taskId, $locks)
    {
        //if(empty($parameters))return true;

        $data = [];
        foreach ($locks as $parameter) {
            $data[] = [
                'task_id' => $taskId,
                'lock_name' => ArkHelper::readTarget($parameter, ['lock_name'], ''),
                'addition' => ArkHelper::readTarget($parameter, ['addition'], ''),
            ];
        }
        $afx = $this->batchInsert($data);
        if (empty($afx)) {
            throw new Exception("Cannot register task locks: " . $this->getPdoLastError());
        }
        return $afx;
    }
}