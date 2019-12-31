# NaiveJob

A Job Schedule Solution with PHP

PHP向任务调度解决方案

## 实现简述

1. 使用PCNTL的多进程架构实现主进程消费任务队列，并分发任务给子进程完成。
    1. 加入任务队列，只需新增一条设置好类型和参数等的记录到对列表；
    2. 队列中只有在入队状态的任务才会被调度消费；
    3. 工作子进程数量可控，用完即扔；
2. 定时任务通过定时向任务队列生产任务实现。
    1. 设定一个每分钟跑一次的Cron Job以允许Schedule Daemon；
    2. 然后在数据库的schedule表中设定定时任务计划；
    3. 每种定时任务需要在队列表中设置好运行任务模板;
    
## API

* 队列 QueueController
    * 获取状况速览 dashboardData
    * 任务队列明细 listTasksInQueue
    * 取消任务 cancelTask
    * 将任务入队 enqueueTask
    * 复制一个任务 forkTask
    * 创建一个任务 createTask
* 规划 ScheduleController
    * 获取规划列表 fetchScheduleList
    * 创建任务模板 createTaskTemplate
    * 创建规划 createSchedule
    * 调整规划状态 switchSchedule
* 控制 SwitchController
    * 获取命令历史 getControlHistory
    * 获取当前命令 getCurrentSwitch
    * 发出命令 switchQueue
    
## 数据库表结构

Schema不限，建议使用UTF8MB4。

建表语句，见 init-db.sql 。