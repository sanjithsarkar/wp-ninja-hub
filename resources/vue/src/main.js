import { createApp } from 'vue';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import App from './App.vue';
import './style.css';

const el = document.getElementById('wp-ninja-hub-app');
if (el) {
    const app = createApp(App);
    app.use(ElementPlus);
    app.mount(el);
}
