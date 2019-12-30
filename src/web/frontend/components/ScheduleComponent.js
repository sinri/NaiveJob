Vue.component(
    'schedule-component',
    {
        data: function () {
            return {
                show_loading_spin: true,
                status_options: ['ON', 'OFF', 'NEVER'],
                status: ['ON', 'OFF'],
                schedules: [],
                total: 0,
                page: 1,
                page_size: 10,
            }
        },
        template: `<div>
                <Row>
                    <i-col span="24">
                        <h1>Schedule</h1>
                    </i-col>
                </Row>
                <Row>
                    <i-col span="24"><h2>Conditions</h2></i-col>
                    <i-col span="24">
                        <Checkbox-Group v-model="status">
                            <Checkbox v-for="(option,index) in status_options" :label="option" :key="index">
<!--                                <Icon type="logo-twitter"></Icon>-->
                                <span>{{option}}</span>
                            </Checkbox>
                        </Checkbox-Group>
                    </i-col>
                    <i-col span="24">
                        <i-button type="primary" @click="loadData" :loading="show_loading_spin">Load</i-button>
                    </i-col>
                    <i-col span="24"><h2>Schedule List</h2></i-col>
                    <i-col span="24">
                        <table style="width: 100%;">
                            <tr>
                                <th>Schedule ID</th>
                                <th>Cron Expression</th>
                                <th>Job Type</th>
                                <th>Job Code</th>
                                <th>Status</th>
                                <th>Parent Task ID</th>
                                <th>Action</th>
                            </tr>
                            <tr v-for="(schedule,index) in schedules" :key="index">
                                <td>{{schedule.schedule_id}}</td>
                                <td>{{schedule.cron_expression}}</td>
                                <td>{{schedule.job_type}}</td>
                                <td>{{schedule.job_code}}</td>
                                <td>{{schedule.status}}</td>
                                <td>{{schedule.parent_task_id}}</td>
                                <td>
                                    <i-button type="info" size="small" @click="change_schedule_status(schedule,schedule.status==='ON'?'OFF':'ON')">TURN {{schedule.status==='ON'?'OFF':'ON'}}</i-button>
                                    <i-button type="error" size="small" @click="fork_to_execute_now(schedule)">Run Once Now</i-button>
                                </td>
                            </tr>
                        </table>
                    </i-col>
                    <i-col span="24">
                        <Page :total="total" :current="page" show-total show-sizer v-on:on-page-size-change="page_size_changed" v-on:on-change="page_changed"/>
                    </i-col>
                </Row>
            </div>`,
        methods: {
            loadData: function () {
                this.show_loading_spin = true;
                let conditions = {
                    page: this.page,
                    page_size: this.page_size,
                };
                if (this.status && this.status.length > 0) conditions.status = this.status;
                SinriQF.api.call(
                    'ScheduleController/fetchScheduleList',
                    conditions,
                    (data) => {
                        this.schedules = data.schedules;
                        this.total = data.total;
                        if ((this.page - 1) * this.page_size > this.total) {
                            console.log("emmm")
                            this.page_changed(1);
                        }
                        this.show_loading_spin = false;
                    },
                    (error, status) => {
                        this.schedules = [];
                        this.total = 0;
                        SinriQF.iview.showErrorMessage(error);
                        this.show_loading_spin = false;
                    }
                );
            },
            page_changed: function (new_page) {
                this.page = new_page;
                this.loadData();
            },
            page_size_changed: function (pageSize) {
                this.page_size = pageSize;
                this.loadData();
            },
            change_schedule_status: function (schedule, new_status) {
                SinriQF.api.call(
                    'ScheduleController/switchSchedule',
                    {
                        schedule_id: schedule.schedule_id,
                        status: new_status,
                    },
                    (data) => {
                        SinriQF.iview.showSuccessMessage("TURN " + new_status + " " + schedule.schedule_id + ' DONE');
                        schedule.status = new_status;
                    },
                    (error, status) => {
                        SinriQF.iview.showErrorMessage(error);
                        // this.show_loading_spin=false;
                    }
                );
            },
            fork_to_execute_now: function (schedule) {
                SinriQF.api.call(
                    'QueueController/forkTask',
                    {
                        task_id: schedule.parent_task_id,
                        enqueue_now: 'YES',
                    },
                    (data) => {
                        SinriQF.iview.showSuccessMessage("Add task to queue and enqueued it: " + data.task_id);
                    },
                    (error, status) => {
                        SinriQF.iview.showErrorMessage(error);
                    }
                );
            }
        },
        mounted: function () {
            this.loadData();
        }
    }
)