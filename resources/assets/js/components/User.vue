<template>
    <el-container>
        <el-header>Header</el-header>
        <el-main>
            <el-row>
                <el-col :span="24">{{ msg }}</el-col>
            </el-row>
            <el-row>
                <el-col :span="24">
                    <el-table
                            v-loading="loading"
                            :data="userData"
                            style="width: 100%">
                        <el-table-column prop="name" label="姓名" ></el-table-column>
                        <el-table-column prop="age" label="年龄" ></el-table-column>
                        <el-table-column prop="date" label="日期" ></el-table-column>
                    </el-table>
                </el-col>
            </el-row>
        </el-main>
        <el-footer>Footer</el-footer>
    </el-container>
</template>

<script>
    export default {
        name: "User",
        data() {
            return {
                msg: 'User Test Content',
                userData: [],
                code: '',
                loading: false
            };
        },
        created() {
            this.getUserData();
        },
        methods: {
            getUserData: function() {
                this.$http.get('/api/test8').then((response) => {
                    this.userData = response.body.result;
                }, (response) => {
                    this.code = response.code;
                });
            }
        }
    }
</script>

<style scoped>
    body {
        font-family: "Helvetica Neue",Helvetica,"PingFang SC","Hiragino Sans GB","Microsoft YaHei","微软雅黑",Arial,sans-serif;
        color: #303133;
    }
    .el-header, .el-footer {
        background-color: #409EFF;
        line-height: 60px;
    }

    .el-main {
        background-color: #DCDFE6;
        line-height: 160px;
    }
</style>