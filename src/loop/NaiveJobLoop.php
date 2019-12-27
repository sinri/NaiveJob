<?php


namespace sinri\NaiveJob\loop;


use sinri\ark\queue\parallel\ParallelQueueDaemon;
use sinri\NaiveJob\loop\model\NaiveJobControlModel;

class NaiveJobLoop extends ParallelQueueDaemon
{
    public function resetSwitchCodeToRun(){
        return (new NaiveJobControlModel())->writeLastControlValue(NaiveJobControlModel::CONTROL_CODE_QUEUE_SWITCH,NaiveJobControlModel::CONTROL_VALUE_QUEUE_SWITCH_RUN);
    }

    public function loop()
    {
        $this->resetSwitchCodeToRun();
        parent::loop();
    }
}