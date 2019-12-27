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
                    <Menu-Item name="3">
                        <Icon type="ios-analytics"></Icon>
                        Item 3
                    </Menu-Item>
                    <Menu-Item name="4">
                        <Icon type="ios-paper"></Icon>
                        Item 4
                    </Menu-Item>
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