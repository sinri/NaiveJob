Vue.component(
    'create-schedule-component',
    {
        data: function () {
            return {
                parent_task_list: [],
                draft: {
                    cron_expression: '',
                    job_code: '',
                    status: '',
                    parent_task_id: '',
                },
                loading_parent_tasks: false,
                loading_submit_draft: false,
            }
        },
        template: `<Row>
                <i-col span="24">
                    <h1>Create Schedule</h1>
                </i-col>
                <i-col span="24">
                    <span style="width: 80px;display: inline-block;text-align: right">Cron</span>
                    <i-input type="text" v-model="draft.cron_expression" style="width: 200px"></i-input>
                </i-col>
                <i-col span="24">
                    <span style="width: 80px;display: inline-block;text-align: right">Job Code</span>
                    <i-input type="text" v-model="draft.job_code" style="width: 200px"></i-input>
                </i-col>
                <i-col span="24">
                    <span style="width: 80px;display: inline-block;text-align: right">Status</span>
                    <i-select v-model="draft.status" style="width: 200px">
                        <i-option value="ON">ON</i-option>
                        <i-option value="OFF">OFF</i-option>
                    </i-select>
                </i-col>
                <i-col span="24">
                    <span style="width: 80px;display: inline-block;text-align: right">Parent Task</span>
                    <i-select v-model="draft.parent_task_id" style="width: 200px" remote :remote-method="loadParentTasks" filterable :loading="loading_parent_tasks">
                        <i-option v-for="(parent_task,index) in parent_task_list" :key="index" :value="parent_task.task_id">{{parent_task.task_id}} - {{parent_task.task_title}}</i-option>
                    </i-select>
<!--                    <i-button type="text" remote @remote-method="loadParentTasks" filterable :loading="loading_parent_tasks" icon="ios-refresh"></i-button>-->
                </i-col>
                <i-col span="24">
                    <i-button type="primary" :loading="loading_submit_draft" @click="submit_draft">Submit</i-button>
                </i-col>
            </Row>`,
        methods: {
            loadParentTasks: function (query) {
                this.loading_parent_tasks = true;
                SinriQF.api.call(
                    'ScheduleController/searchTemplateTask',
                    {
                        keyword: query
                    },
                    (data) => {
                        this.parent_task_list = data['template_task_list'];
                        this.loading_parent_tasks = false;
                    },
                    (error, status) => {
                        //this.tasks=[];
                        //this.total=0;
                        SinriQF.iview.showErrorMessage(error);
                        this.loading_parent_tasks = false;
                    }
                );
            },
            submit_draft: function () {
                this.loading_submit_draft = true;
                SinriQF.api.call(
                    'ScheduleController/createSchedule',
                    {
                        cron_expression: this.draft.cron_expression,
                        job_code: this.draft.job_code,
                        status: this.draft.status,
                        parent_task_id: this.draft.parent_task_id,
                    },
                    (data) => {
                        SinriQF.iview.showSuccessMessage("Created Schedule " + data.schedule_id);
                        this.clear_draft();
                        this.loading_submit_draft = false;
                    },
                    (error, status) => {
                        SinriQF.iview.showErrorMessage(error);
                        this.loading_submit_draft = false;
                    }
                );
            },
            clear_draft: function () {
                this.draft = {
                    cron_expression: '',
                    job_code: '',
                    status: '',
                    parent_task_id: '',
                };
            }
        }
    }
);