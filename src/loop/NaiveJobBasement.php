<?php


namespace sinri\NaiveJob\loop;


use Exception;
use Psr\Log\LogLevel;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\queue\parallel\ParallelQueueTask;
use sinri\NaiveJob\loop\model\NaiveJobParametersModel;

abstract class NaiveJobBasement extends ParallelQueueTask
{
    /**
     * @var ArkLogger
     */
    protected $logger;
    protected $taskRow;

    protected $parameters;

    /**
     * NaiveJobBasement constructor.
     * @param array $row
     * @throws Exception
     */
    public function __construct($row)
    {
        parent::__construct();
        $this->taskRow=$row;

        //logger
        $path = Ark()->readConfig(['log', 'path']);
        if ($path !== null) {
            $logger = new ArkLogger($path.'/naive-job-tasks', 'task-'.$row['task_id'],null);
            $level = Ark()->readConfig(['log', 'level'], LogLevel::INFO);
            $logger->setIgnoreLevel($level);
//            $logger->setGroupByPrefix(true);
        } else {
            $logger = ArkLogger::makeSilentLogger();
        }
        $this->logger=$logger;

        //parameters
        $prs=(new NaiveJobParametersModel())->selectRows(['task_id'=>$this->getTaskReference()]);
        $this->parameters=[];
        foreach ($prs as $pr){
            $this->parameters[$pr['name']]=$pr['value'];
        }
    }

    /**
     * @inheritDoc
     */
    public function getTaskReference()
    {
        return $this->taskRow['task_id'];
    }

    /**
     * @inheritDoc
     */
    public function getTaskType()
    {
        return $this->taskRow['task_type'];
    }

    /**
     * @return bool
     * Run in parent process
     */
//    public function beforeExecute()
//    {
//        $this->readyToExecute = true;
//        return $this->readyToExecute;
//    }
//
//    public function afterExecute()
//    {
//        $this->readyToFinish = true;
//        return $this->readyToFinish;
//    }

    /**
     * @inheritDoc
     */
//    public function execute();
//    {
//        sleep(5);
//        $result=rand(1,9);
//        if($result>6){
//            $this->done=false;
//            $this->executeFeedback="Error-".$result;
//            $this->executeResult=$result;
//        }else{
//            $this->done=true;
//            $this->executeFeedback="Done-".$result;
//            $this->executeResult=$result;
//        }
//    }

    public function readParameters($name,$default=null){
        return ArkHelper::readTarget($this->parameters,[$name],$default);
    }

    /**
     * @param array $row
     * @return NaiveJobBasement
     * @throws Exception
     */
    public static function factory($row){
        $type=$row['task_type'];

        $className=__NAMESPACE__.'\\executor\\NaiveJobWith'.$type;
        if(!class_exists($className)){
            throw new Exception("This type may not be supported yet, class ".$className." could not be found");
        }

        return new $className($row);
    }
}