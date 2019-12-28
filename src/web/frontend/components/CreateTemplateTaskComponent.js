Vue.component(
    'create-template-task-component',
    {
        data: function () {
            return {
                task_draft: {
                    task_title: '',
                    task_type: '',
                    priority: 10,
                },
                parameters: {
                    command: '',
                },
                submitting: false,
            };
        },
        template: `<Row>
                <i-col span="24"><h1>Create Template Task</h1></i-col>
                <i-col span="24">
                    <span style="width: 80px;display: inline-block;text-align: right">Title</span>
                    <i-input v-model="task_draft.task_title" style="width: 200px" inline></i-input>
                </i-col>
                <i-col span="24">
                    <span style="width: 80px;display: inline-block;text-align: right">Type</span>
                    <i-select v-model="task_draft.task_type" style="width: 200px" inline>
                        <i-option value="Bash"></i-option>
                    </i-select>
                </i-col>
                <i-col span="24">
                    <span style="width: 80px;display: inline-block;text-align: right">Priority</span>
                    <Input-Number v-model="task_draft.priority" :max="20" :min="1" style="width: 200px" inline></Input-Number>
                </i-col>
                <i-col span="24">
                    <h2>Parameters</h2>
                </i-col>
                <i-col span="24" v-show="task_draft.task_type=='Bash'">
                    <span style="width: 80px;display: inline-block;text-align: right">Command</span>
                    <i-input v-model="parameters.command" style="width: 400px" inline></i-input>
                </i-col>
                <i-col span="24">
                    <i-button type="primary" :loading="submitting" @click="submit_draft">Submit</i-button>
                </i-col>
            </Row>`,
        methods: {
            submit_draft: function () {
                this.submitting = true;
                const conditions = {
                    task_title: this.task_draft.task_title,
                    task_type: this.task_draft.task_type,
                    priority: this.task_draft.priority,
                    parameters: {
                        command: this.parameters.command
                    }
                };
                SinriQF.api.call(
                    'ScheduleController/createTaskTemplate',
                    conditions,
                    (data) => {
                        SinriQF.iview.showSuccessMessage("Created " + data.task_id);
                        this.submitting = false;
                        this.clear_draft();
                    },
                    (error, status) => {
                        SinriQF.iview.showErrorMessage(error);
                        this.submitting = false;
                    }
                );
            },
            clear_draft: function () {
                this.task_draft = {
                    task_title: '',
                    task_type: 'Bash',
                    priority: 10,
                };
                this.parameters = {
                    command: '',
                };
            }
        }
    }
)