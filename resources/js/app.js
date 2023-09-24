import "./bootstrap";
import "./assets/css/app.css";
import "../css/custom.css";

import { createApp } from "vue";
import { createPinia } from "pinia";
import { createI18n } from "vue-i18n";
import router from "./router";
import App from "./App.vue";
import globalComponents from "./global-components";
import utils from "./utils";
import { languages, defaultLocale } from './lang'


const pinia = createPinia();
const app = createApp(App);

// Translations
const currentLocale = window.appModule.currentLocale;

// const messages = {
//     [currentLocale]: window.appModule.translations,
// };

const messages = Object.assign(languages)
const i18n = createI18n({
    locale: currentLocale,
    fallbackLocale: defaultLocale,
    messages,
});

globalComponents(app);
utils(app);

app.use(pinia).use(i18n).use(router);

app.mount("#app");
