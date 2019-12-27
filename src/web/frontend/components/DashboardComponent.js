Vue.component(
    'dashboard-component',
    {
        data:function(){
            return {
                dashboard_data:null,
                show_loading_spin:true,
            }
        },
        template:`<div> 
                <Row style="margin: 5px">
                    <i-col span="24"><h3>Dashboard</h3></i-col>
                    <i-col span="24">
                        <i-button @click="loadData" icon="ios-refresh" :loading="show_loading_spin">Refresh</i-button>
                        <Spin v-show="show_loading_spin"></Spin>
                    </i-col>
                </Row>
                <div v-if="dashboard_data!==null">
                    <template v-if="!!dashboard_data.status_stat">
                        <p>Task Stat by Status</p>
                        <ul>
                            <li v-for="item in dashboard_data.status_stat">{{item.status}} : {{item.number}}</li>
                        </ul>
                    </template>
                </div>
            </div>`,
        methods:{
            loadData:function(){
                this.show_loading_spin=true;
                SinriQF.api.call(
                    'QueueController/dashboardData',
                    {

                    },
                    (data)=>{
                        console.log('before',this.dashboard_data)
                        this.dashboard_data=data;
                        console.log('after',this.dashboard_data)
                        this.show_loading_spin=false;
                    },
                    (error,status)=>{
                        this.dashboard_data="Error: "+error+" | status: "+status;
                        SinriQF.iview.showErrorMessage(error);
                        this.show_loading_spin=false;
                    }
                    );
            }
        },
        mounted:function () {
            this.loadData();
        }
    }
);