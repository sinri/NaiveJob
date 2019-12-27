<?php


namespace sinri\NaiveJob\web\controller;


use Exception;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\web\implement\ArkWebController;
use sinri\NaiveJob\loop\model\NaiveJobControlModel;

class SwitchController extends ArkWebController
{
    protected $logger;
    protected $controlModel;

    public function __construct()
    {
        parent::__construct();

        $this->logger=Ark()->logger('web');
        $this->controlModel=new NaiveJobControlModel(false);
    }

    /**
     * 获取控制命令发布历史
     */
    public function getControlHistory(){
        try{
            $code=$this->_readRequest('code');
            $start_time=$this->_readRequest('start_time');
            $end_time=$this->_readRequest('end_time');
            $page=$this->_readRequest('page',1);
            $page_size=$this->_readRequest('page_size',10);

            $conditions=[];
            if($code!==null)$conditions['code']=$code;
            if($start_time!==null)$conditions['start_time']=ArkSQLCondition::makeNoLessThan('control_time',$start_time);
            if($end_time!==null)$conditions['end_time']=ArkSQLCondition::makeLessThan('control_time',$end_time);

            $total=$this->controlModel->selectRowsForCount($conditions);
            $rows=$this->controlModel->selectRowsWithSort($conditions,NaiveJobControlModel::standardSortExpression(),$page_size,($page-1)*$page_size);
            if($rows===false){
                throw new Exception("Cannot fetch control history: ".$this->controlModel->getPdoLastError());
            }

            $this->_sayOK(['rows'=>$rows,'total'=>$total]);
        }catch (Exception $exception){
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 获取最近生效中的开关值
     */
    public function getCurrentSwitch(){
        try{
            $controlValue=$this->controlModel->readLastControlValue('QUEUE_SWITCH',NaiveJobControlModel::CONTROL_VALUE_QUEUE_SWITCH_RUN,$controlTime);
            $this->_sayOK(['control_value'=>$controlValue,'control_time'=>$controlTime]);

        }catch (Exception $exception){
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 变更开关的值
     */
    public function switchQueue(){
        try{
            $controlValue=$this->_readRequest('control_value');
            $controlTime=$this->_readRequest('control_time');

            $afx=$this->controlModel->writeLastControlValue('QUEUE_SWITCH',$controlValue,$controlTime);
            if(!$afx){
                throw new Exception("cannot switch queue: ".$this->controlModel->getPdoLastError());
            }
            $this->_sayOK(['afx'=>$afx]);
        }catch (Exception $exception){
            $this->_sayFail($exception->getMessage());
        }
    }
}