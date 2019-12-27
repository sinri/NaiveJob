<?php


namespace sinri\NaiveJob\loop\model;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\NaiveJob\core\NaiveJobCore;
use sinri\NaiveJob\core\NaiveJobTableModel;

class NaiveJobParametersModel extends NaiveJobTableModel
{

    /**
     * @inheritDoc
     */
    public function mappingTableName()
    {
        return "naive_job_parameters";
    }

    /**
     * @param $taskId
     * @param $parameters
     * @return bool|string
     * @throws Exception
     */
    public function batchRegisterParametersForTask($taskId,$parameters){
        //if(empty($parameters))return true;

        $data = [];
        foreach ($parameters as $parameter) {
            $data[] = [
                'task_id' => $taskId,
                'name' => $parameter['name'],
                'value' => $parameter['value'],
            ];
        }
        $afx = $this->batchInsert($data);
        if (empty($afx)) {
            throw new Exception("Cannot register task parameters: ".$this->parameterModel->getPdoLastError());
        }
        return $afx;
    }
}