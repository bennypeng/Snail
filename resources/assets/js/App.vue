<template>
    <div>
        <el-menu
                :default-active="activeIndex"
                class="el-menu--horizontal el-menu"
                mode="horizontal"
                @select="handleSelect">
            <el-menu-item index="1">
                <router-link to="/">HomePage</router-link>
            </el-menu-item>
            <el-menu-item index="2">
                <router-link to="/hello">HelloPage</router-link>
            </el-menu-item>
            <el-menu-item index="3">
                <router-link to="/user">UserPage</router-link>
            </el-menu-item>
        </el-menu>

        <!--路由引入的组件将在这里被渲染-->
        <router-view></router-view>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                activeIndex: '1'
            }
        },
        methods: {
            handleSelect(key, keyPath) {
                console.log(key, keyPath);
            }
        }
    }
</script>

<style scoped>

</style>