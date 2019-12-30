Vue.component(
    'queue-component',
    {
        data:function () {
            return {
                status_options: ['INIT', 'ENQUEUED', 'RUNNING', 'DONE', 'ERROR', 'CANCELLED', 'TEMPLATE'],
                status: ['INIT', 'ENQUEUED', 'RUNNING', 'DONE', 'ERROR', 'CANCELLED'],
                task_id: '',
                parent_task_id: '',
                tasks: [],
                total: 0,
                page: 1,
                page_size: 10,
                show_loading_spin: true,
            }
        },
        template: `<div>
                <Row>
                    <i-col span="24">
                        <h1>Queue</h1>
                    </i-col>
                </Row>
                <Row>
                    <i-col span="24"><h2>Conditions</h2></i-col>
                    <i-col span="24">
                        <span>Status</span>
                        <Checkbox-Group v-model="status">
                            <!-- INIT ENQUEUED RUNNING DONE ERROR CANCELLED -->
                            <Checkbox v-for="(option,index) in status_options" :label="option" :key="index">
<!--                                <Icon type="logo-twitter"></Icon>-->
                                <span>{{option}}</span>
                            </Checkbox>
                        </Checkbox-Group>
                    </i-col>
                    <i-col span="24">
                        <span>Task ID</span>
                        <i-input type="text" v-model="task_id" style="width: 150px"></i-input>
                    </i-col>
                    <i-col span="24">
                        <span>Parent Task ID</span>
                        <i-input type="text" v-model="parent_task_id" style="width: 150px"></i-input>
                    </i-col>
                    <i-col span="24">
                        <i-button type="primary" @click="loadData" :loading="show_loading_spin">Search</i-button>
                    </i-col>
                </Row>
                <Row>
                    <i-col span="24"><h2>Task List</h2></i-col>
                    <i-col span="24">
                        <table style="width: 100%;">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Apply</th>
                                <th>Enqueue</th>
                                <th>Execute</th>
                                <th>Finish</th>
                                <th>Feedback</th>
                                <th>Process</th>
                                <th>Parent</th>
                                <th>Action</th>
                            </tr>
                            <tr v-for="task in tasks">
                                <td>{{task.task_id}}</td>
                                <td>{{task.task_title}}</td>
                                <td>{{task.task_type}}</td>
                                <td>{{task.status}}</td>
                                <td>{{task.priority}}</td>
                                <td>{{task.apply_time}}</td>
                                <td>{{task.enqueue_time}}</td>
                                <td>{{task.execute_time}}</td>
                                <td>{{task.finish_time}}</td>
                                <td><div style="white-space: pre-line;">{{task.feedback}}</div></td>
                                <td>{{task.pid}}</td>
                                <td>{{task.parent_task_id}}</td>
                                <td>
                                    <i-button type="warning" size="small" v-if="task.status==='INIT' || task.status==='ENQUEUED'" @click="cancel_task(task)">Cancel</i-button>
                                    <i-button type="success" size="small" v-if="task.status==='INIT' || task.status==='CANCELLED' || task.status==='ERROR'" @click="enqueue_task(task)">Enqueue</i-button>
                                    <i-button type="info" size="small" v-if="task.status==='DONE' || task.status==='TEMPLATE'" @click="fork_task(task)">Fork</i-button>
                                </td>
                            </tr>
                        </table>
                    </i-col>
                </Row>
                <Row>
                    <i-col span="24">
                        <Page :total="total" :current="page" show-total show-sizer show-elevator v-on:on-page-size-change="page_size_changed" v-on:on-change="page_changed"/>
                    </i-col>
                </Row>
            </div>`,
        methods:{
            loadData:function() {
                this.show_loading_spin = true;
                let conditions = {
                    page: this.page,
                    page_size: this.page_size,
                };
                if (this.status && this.status.length > 0) conditions.status = this.status;
                if (this.task_id && this.task_id > 0) conditions.task_id = this.task_id;
                if (this.parent_task_id && this.parent_task_id > 0) conditions.parent_task_id = this.parent_task_id;
                SinriQF.api.call(
                    'QueueController/listTasksInQueue',
                    conditions,
                    (data) => {
                        this.tasks = data.tasks;
                        this.total = data.total;
                        if ((this.page - 1) * this.page_size > this.total) {
                            console.log("emmm")
                            this.page_changed(1);
                        }
                        this.show_loading_spin = false;
                    },
                    (error,status)=>{
                        //this.tasks=[];
                        //this.total=0;
                        SinriQF.iview.showErrorMessage(error);
                        this.show_loading_spin=false;
                    }
                );
            },
            page_changed: function (new_page) {
                console.log('page_changed -> ', new_page)
                this.page = new_page;
                this.loadData();
            },
            page_size_changed: function (pageSize) {
                this.page_size = pageSize;
                this.loadData();
            },
            cancel_task: function (task) {
                SinriQF.api.call(
                    'QueueController/cancelTask',
                    {
                        task_id: task.task_id
                    },
                    (data) => {
                        // this.tasks=data.tasks;
                        // this.total=data.total;
                        // this.show_loading_spin=false;
                        SinriQF.iview.showSuccessMessage("Cancelled " + task.task_id);
                        task.status = 'CANCELLED';
                    },
                    (error, status) => {
                        //this.tasks=[];
                        //this.total=0;
                        SinriQF.iview.showErrorMessage(error);
                        // this.show_loading_spin=false;
                    }
                );
            },
            enqueue_task: function (task) {
                SinriQF.api.call(
                    'QueueController/enqueueTask',
                    {
                        task_id: task.task_id
                    },
                    (data) => {
                        // this.tasks=data.tasks;
                        // this.total=data.total;
                        // this.show_loading_spin=false;
                        SinriQF.iview.showSuccessMessage("Enqueued " + task.task_id);
                        task.status = 'ENQUEUED';
                    },
                    (error, status) => {
                        //this.tasks=[];
                        //this.total=0;
                        SinriQF.iview.showErrorMessage(error);
                        // this.show_loading_spin=false;
                    }
                );
            },
            fork_task: function (task) {
                SinriQF.api.call(
                    'QueueController/forkTask',
                    {
                        task_id: task.task_id,
                        enqueue_now: 'NO',
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
        mounted:function () {
            this.loadData();
        }
    }
)