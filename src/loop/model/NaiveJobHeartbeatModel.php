<?php


namespace sinri\NaiveJob\loop\model;


use sinri\NaiveJob\core\NaiveJobTableModel;

class NaiveJobHeartbeatModel extends NaiveJobTableModel
{

    /**
     * @inheritDoc
     */
    public function mappingTableName()
    {
        return "naive_job_heartbeat";
    }

    /**
     * @param string $object
     * @param string $code
     * @param string $message
     * @return bool|string
     */
    public function beat($object,$code,$message){
        return $this->replace([
            'object'=>$object,
            'code'=>$code,
            'message'=>$message,
            'beat_time'=>self::now(),
        ]);
    }
}