CREATE TABLE `naive_job_control`
(
    `control_id`    bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `control_code`  varchar(128)        NOT NULL DEFAULT '',
    `control_value` varchar(128)        NOT NULL DEFAULT '',
    `control_time`  datetime            NOT NULL,
    PRIMARY KEY (`control_id`),
    KEY `control_code` (`control_code`, `control_time`, `control_id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `naive_job_heartbeat`
(
    `id`        bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `object`    varchar(128)        NOT NULL DEFAULT '',
    `code`      varchar(128)        NOT NULL DEFAULT '',
    `message`   varchar(512)                 DEFAULT NULL,
    `beat_time` datetime            NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `object` (`object`, `code`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `naive_job_lock`
(
    `lock_id`   bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `task_id`   bigint(20)          NOT NULL,
    `lock_name` varchar(128)        NOT NULL DEFAULT '',
    `addition`  varchar(128)                 DEFAULT NULL,
    PRIMARY KEY (`lock_id`),
    UNIQUE KEY `task_id` (`task_id`, `lock_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `naive_job_parameters`
(
    `id`      bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `task_id` bigint(20)          NOT NULL,
    `name`    varchar(128)        NOT NULL DEFAULT '',
    `value`   varchar(1024)       NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY `task_id` (`task_id`, `name`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `naive_job_queue`
(
    `task_id`        bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `task_title`     varchar(128)        NOT NULL DEFAULT '',
    `task_type`      varchar(128)        NOT NULL DEFAULT '',
    `status`         varchar(32)         NOT NULL DEFAULT '' COMMENT 'INIT ENQUEUED RUNNING DONE ERROR CANCELLED',
    `priority`       int(11)                      DEFAULT NULL COMMENT 'MAX FIRST',
    `apply_time`     datetime                     DEFAULT NULL,
    `enqueue_time`   datetime                     DEFAULT NULL,
    `execute_time`   datetime                     DEFAULT NULL,
    `finish_time`    datetime                     DEFAULT NULL,
    `feedback`       mediumtext,
    `pid`            int(11)                      DEFAULT NULL COMMENT 'MINUS AS DISMISSED',
    `parent_task_id` int(11)                      DEFAULT NULL COMMENT '0 or NULL no parent task',
    PRIMARY KEY (`task_id`),
    KEY `status` (`status`, `priority`, `enqueue_time`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `naive_job_schedule`
(
    `schedule_id`     bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `cron_expression` varchar(128)        NOT NULL DEFAULT '',
    `job_type`        varchar(128)        NOT NULL DEFAULT '',
    `job_code`        varchar(32)         NOT NULL DEFAULT '',
    `status`          varchar(16)         NOT NULL DEFAULT 'OFF' COMMENT 'ON OFF',
    `parent_task_id`  bigint(20)                   DEFAULT NULL,
    PRIMARY KEY (`schedule_id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;