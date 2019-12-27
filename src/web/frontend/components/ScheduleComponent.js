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
                    <i-col span="24">
                        <i-button type="primary" @click="loadData" :loading="show_loading_spin">Load</i-button>
                    </i-col>
                    <i-col span="24">
                        <Checkbox-Group v-model="status">
                            <Checkbox v-for="(option,index) in status_options" :label="option" :key="index">
<!--                                <Icon type="logo-twitter"></Icon>-->
                                <span>{{option}}</span>
                            </Checkbox>
                        </Checkbox-Group>
                    </i-col>
                    <i-col span="24">
                        <table>
                            <tr>
                                <th>schedule_id</th>
                                <th>cron_expression</th>
                                <th>job_type</th>
                                <th>job_code</th>
                                <th>status</th>
                                <th>parent_task_id</th>
                            </tr>
                            <tr v-for="(schedule,index) in schedules" :key="index">
                                <td>{{schedule.schedule_id}}</td>
                                <td>{{schedule.cron_expression}}</td>
                                <td>{{schedule.job_type}}</td>
                                <td>{{schedule.job_code}}</td>
                                <td>{{schedule.status}}</td>
                                <td>{{schedule.parent_task_id}}</td>
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
            }
        },
        mounted: function () {
            this.loadData();
        }
    }
)