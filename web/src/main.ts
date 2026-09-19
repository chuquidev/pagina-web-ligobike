import { createApp } from "vue";
import { createPinia } from "pinia";
import { createHead } from "@unhead/vue/client";
import { registerSW } from "virtual:pwa-register";
import App from "./App.vue";
import router from "./router";
import "./style.css";

const app = createApp(App);
const head = createHead();

app.use(createPinia());
app.use(head);
app.use(router);
app.mount("#app");

registerSW({
  immediate: true,
  onNeedRefresh() {
    window.location.reload();
  },
});
