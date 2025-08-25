// import './bootstrap';
// import '../css/app.css'; // ✅ import CSS ở đây

// import { createApp } from 'vue';
// import ExampleComponent from './components/ExampleComponent.vue';

// const app = createApp({});
// app.component('example-component', ExampleComponent);
// app.mount('#app');


// import { createApp } from 'vue'
// import App from './App.vue'
// import router from './router'
// import './bootstrap'
// import '../css/app.css'; // ✅ import CSS ở đây

// createApp(App).use(router).mount('#app')

import { createApp } from "vue"
import { createPinia } from "pinia"
import router from "./router/index.js"
import App from "./App.vue"

// Import Bootstrap CSS
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap/dist/js/bootstrap.bundle.min.js"

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

app.mount("#app")
