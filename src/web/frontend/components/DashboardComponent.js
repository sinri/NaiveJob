Vue.component(
    'dashboard-component',
    {
        data:function(){
            return {
                dashboard_data: null,
                show_loading_spin: true,
                show_loading_switch_value_span: true,
                queue_switch_value: false,
                queue_switch_value_time: '',
                queue_switch_value_changing: false,
            }
        },
        template:`<div> 
                <Row style="margin: 5px">
                    <i-col span="24"><h1>Dashboard</h1></i-col>
                    
                </Row>
                <Row>
                    <i-col span="24"><h2>Switch</h2></i-col>
                    <i-col span="24">
                        <span>Current:</span> <span>{{queue_switch_value}} since {{queue_switch_value_time}}</span>
                        <i-button type="info" icon="ios-refresh" :loading="show_loading_switch_value_span" @click="refreshSwitchValue">Refresh</i-button>
<!--                        <i-switch :loading="queue_switch_value_changing" v-model="queue_switch_value" @on-change="queue_switch_value_changed"></i-switch>-->
                        <span>Action:</span>
                        <Button-Group>
                            <i-button type="error" @click="queue_switch_value_changed('STOP')">STOP</i-button>
                            <i-button type="warning" @click="queue_switch_value_changed('SLEEP')">SLEEP</i-button>
                            <i-button type="primary" @click="queue_switch_value_changed('RUN')">RUN</i-button>
                        </Button-Group>
                    </i-col>
                </Row>
                <Row>
                    <i-col span="24"><h2>Status</h2></i-col>
                    <i-col span="24">
                        <i-button type="info" @click="loadData" icon="ios-refresh" :loading="show_loading_spin">Refresh</i-button>
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
                    {},
                    (data) => {
                        this.dashboard_data = data;
                        this.show_loading_spin = false;
                    },
                    (error, status) => {
                        this.dashboard_data = "Error: " + error + " | status: " + status;
                        SinriQF.iview.showErrorMessage(error);
                        this.show_loading_spin = false;
                    }
                );
            },
            refreshSwitchValue: function () {
                this.show_loading_switch_value_span = true;
                SinriQF.api.call(
                    'SwitchController/getCurrentSwitch',
                    {},
                    (data) => {
                        this.queue_switch_value = data['control_value'];
                        this.queue_switch_value_time = data['control_time'];
                        this.show_loading_switch_value_span = false;
                    },
                    (error, status) => {
                        this.queue_switch_value = "RETRY";
                        SinriQF.iview.showErrorMessage(error);
                        this.show_loading_switch_value_span = false;
                    }
                )
            },
            queue_switch_value_changed: function (newValue) {
                this.queue_switch_value_changing = true;
                SinriQF.api.call(
                    'SwitchController/switchQueue',
                    {
                        'control_value': newValue
                    },
                    (data) => {
                        this.queue_switch_value_changing = false;
                        this.refreshSwitchValue();
                    },
                    (error, status) => {
                        SinriQF.iview.showErrorMessage(error);
                        this.queue_switch_value_changing = false;
                        this.refreshSwitchValue();
                    }
                )
            }
        },
        mounted:function () {
            this.loadData();
            this.refreshSwitchValue();
        }
    }
);