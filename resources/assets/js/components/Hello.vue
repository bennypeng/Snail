<template>
    <div>
        <el-button @click="dialogVisible = true">点击打开</el-button>
        <el-dialog width="30%"
                   :visible.sync="dialogVisible">
            <span>欢迎使用 Element UI</span>
        </el-dialog>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                dialogVisible: false
            };
        }
    }
</script>

<style scoped>

</style>