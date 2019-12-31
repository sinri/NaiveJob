<?php


namespace sinri\NaiveJob\loop;


use Exception;
use Psr\Log\LogLevel;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\queue\parallel\ParallelQueueTask;
use sinri\NaiveJob\core\NaiveJobCore;
use sinri\NaiveJob\loop\model\NaiveJobLockModel;
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

    public function readParameters($name,$default=null){
        return ArkHelper::readTarget($this->parameters,[$name],$default);
    }

    /**
     * @param array $row
     * @return NaiveJobBasement
     * @throws Exception
     */
    public static function factory($row)
    {
        $type = $row['task_type'];

        $className = __NAMESPACE__ . '\\executor\\NaiveJobWith' . $type;
        if (!class_exists($className)) {
            throw new Exception("This type may not be supported yet, class " . $className . " could not be found");
        }

        return new $className($row);
    }

    private $blockingLocks = null;

    /**
     * @return bool
     * @throws Exception
     */
    public function checkIfLocked()
    {
        $sql = "select distinct njl.lock_name
            from naive_job_queue njq
            inner join naive_job_lock njl on njq.task_id = njl.task_id
            where njq.status='RUNNING'";
        $currentLocks = NaiveJobCore::getNewLoopDb()->getAll($sql);
        if (empty($currentLocks)) {
            $this->blockingLocks = [];
            return false;
        }

        $requiredLocks = $this->getTaskReference();

        $this->blockingLocks = array_intersect($requiredLocks, $currentLocks);
        if (empty($this->blockingLocks)) {
            return false;
        }

        return true;
    }

    public function getBlockingLocks()
    {
        return $this->blockingLocks;
    }

    /**
     * @return string[]
     */
    public function getLockList()
    {
        $locks = (new NaiveJobLockModel())->selectRows(['task_id' => $this->getTaskReference()]);
        if (empty($locks)) return [];

        return array_column($locks, 'lock_name');
    }
}