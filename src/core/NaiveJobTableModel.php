<?php


namespace sinri\NaiveJob\core;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;

abstract class NaiveJobTableModel extends ArkDatabaseTableModel
{
    protected $useIndependentPdo;
    /**
     * @var ArkPDO
     */
    protected $pdo;

    public function __construct($useIndependentPdo=true)
    {
        $this->useIndependentPdo=$useIndependentPdo;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public final function db()
    {
        if($this->pdo===null) {
            if ($this->useIndependentPdo) {
                $this->pdo= NaiveJobCore::getNewLoopDb();
            }
            else{
                $this->pdo= Ark()->pdo("loop");
            }
        }
        return $this->pdo;
    }

    public function getPdoLastError(){
        return $this->pdo->getPDOErrorDescription();
    }
}