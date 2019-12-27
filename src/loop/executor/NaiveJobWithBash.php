<?php
namespace sinri\NaiveJob\loop\executor;

use sinri\NaiveJob\loop\NaiveJobBasement;

class NaiveJobWithBash extends NaiveJobBasement
{
    const PARAM_COMMAND="command";

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $command=$this->readParameters(self::PARAM_COMMAND,"echo ".escapeshellarg("NO PARAM FOUND"));
        $command=trim($command);
        $command.=" 2>&1";
        $this->logger->info("The command to execute: ".$command);

        exec($command,$output,$return_value);

        if($return_value===0){
            $this->done=true;
        }else{
            $this->done=false;
        }
        $this->executeFeedback=implode(PHP_EOL,$output);
        $this->executeResult=$return_value;

        $this->logger->smartLogLite($this->done,'← Return value / Output as below ↓',['return'=>$this->executeResult]);
        $this->logger->logInline($this->executeFeedback.PHP_EOL);
        return $this->done;
    }

}