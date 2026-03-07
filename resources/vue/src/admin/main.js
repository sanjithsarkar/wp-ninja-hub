import { createApp } from 'vue';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import AdminApp from './AdminApp.vue';

const el = document.getElementById('wp-ninja-hub-admin-app');
if (el) {
    const app = createApp(AdminApp);
    app.use(ElementPlus);
    app.mount(el);
}
