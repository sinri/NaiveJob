Vue.component(
    'nav-menu-component',
    {
        data:function(){
            return {
                selected_menu_item_name:'dashboard'
            }
        },
        template: `<i-menu mode="horizontal" theme="dark" :active-name="selected_menu_item_name" v-on:on-select="nav_menu_selected">
                <div class="layout-logo" style="color: white;line-height: 30px;text-align: center;">
                    NaiveJob
                </div>
                <div class="layout-nav">
                    <Menu-Item name="dashboard">
                        <Icon type="ios-navigate"></Icon>
                        Dashboard
                    </Menu-Item>
                    <Menu-Item name="queue">
                        <Icon type="ios-keypad"></Icon>
                        Queue
                    </Menu-Item>
<!--                    <Menu-Item name="schedule">-->
<!--                        <Icon type="ios-analytics"></Icon>-->
<!--                        Schedule-->
<!--                    </Menu-Item>-->
                    <Submenu name="ScheduleSubmenu">
                        <template slot="title">
                            <Icon type="ios-stats" />
                            Schedule
                        </template>
                        <Menu-Group title="View">
                            <Menu-Item name="schedule">Schedule List</Menu-Item>
                        </Menu-Group>
                        <MenuGroup title="Edit">
                            <Menu-Item name="create_template_task">New Template Task</Menu-Item>
                            <Menu-Item name="create_schedule">New Schedule</Menu-Item>
                        </MenuGroup>
                    </Submenu>
                </div>
            </i-menu>`,
        methods:{
            nav_menu_selected:function (name) {
                //console.log("nav_menu_selected",name);
                this.selected_menu_item_name=name;
                this.$emit('nav_menu_selected',name);
            }
        }
    }
)