import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

export default new VueRouter({
    saveScrollPosition: true,
    routes: [
        {
            path: '/',
            component: resolve => void(require(['../components/Home.vue'], resolve))
        },
        {
            path: '/hello',
            component: resolve => void(require(['../components/Hello.vue'], resolve))
        },
        {
            path: '/user',
            component: resolve => void(require(['../components/User.vue'], resolve))
        }
    ]
});