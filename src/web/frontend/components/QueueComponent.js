Vue.component(
    'queue-component',
    {
        data:function () {
            return {
                status_options:['INIT', 'ENQUEUED', 'RUNNING', 'DONE', 'ERROR', 'CANCELLED'],
                status:[],
                tasks:[],
                total:0,
                page:1,
                page_size:10,
                show_loading_spin:true,
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
                        <Checkbox-Group v-model="status">
                            <!-- INIT ENQUEUED RUNNING DONE ERROR CANCELLED -->
                            <Checkbox v-for="(option,index) in status_options" :label="option" :key="index">
<!--                                <Icon type="logo-twitter"></Icon>-->
                                <span>{{option}}</span>
                            </Checkbox>
                        </Checkbox-Group>
                    </i-col>
                    <i-col span="24">
                        <i-button type="primary" @click="loadData" :loading="show_loading_spin">Load</i-button>
                    </i-col>
                </Row>
                <Row>
                    <i-col span="24">
                        <table>
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
                                <td>{{task.feedback}}</td>
                                <td>{{task.pid}}</td>
                                <td>{{task.parent_task_id}}</td>
                                <td>...</td>
                            </tr>
                        </table>
                    </i-col>
                </Row>
                <Row>
                    <i-col span="24">
                        <Page :total="total" :current="page" show-total show-sizer v-on:on-page-size-change="page_size_changed" v-on:on-change="page_changed"/>
                    </i-col>
                </Row>
            </div>`,
        methods:{
            loadData:function(){
                this.show_loading_spin=true;
                let conditions={
                    page:this.page,
                    page_size:this.page_size,
                };
                if(this.status && this.status.length>0)conditions.status=this.status;
                SinriQF.api.call(
                    'QueueController/listTasksInQueue',
                    conditions,
                    (data)=>{
                        this.tasks=data.tasks;
                        this.total=data.total;
                        this.show_loading_spin=false;
                    },
                    (error,status)=>{
                        //this.tasks=[];
                        //this.total=0;
                        SinriQF.iview.showErrorMessage(error);
                        this.show_loading_spin=false;
                    }
                );
            },
            page_changed:function(new_page){
                this.page=new_page;
                this.loadData();
            },
            page_size_changed:function (pageSize) {
                this.page_size=pageSize;
                this.loadData();
            }
        },
        mounted:function () {
            this.loadData();
        }
    }
)