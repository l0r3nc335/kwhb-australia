// import Echo from "laravel-echo";
// import Pusher from "pusher-js";
// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: 'reverb',
//     key: import.meta.env.VITE_REVERB_APP_KEY,
//     wsHost: import.meta.env.VITE_REVERB_HOST,
//     wsPort: import.meta.env.VITE_REVERB_PORT,
//     wssPort: import.meta.env.VITE_REVERB_PORT,
//     forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

/* eslint-disable import/order */
import '@/@iconify/icons-bundle'
import App from '@/App.vue'
import layoutsPlugin from '@/plugins/layouts'
import { QuillEditor } from '@/plugins/quill-editor'
import { LoadingPlugin } from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import ToastPlugin from "vue-toast-notification"
import 'vue-toast-notification/dist/theme-bootstrap.css'
import Vue3Toasity from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import vuetify from '@/plugins/vuetify'
import { loadFonts } from '@/plugins/webfontloader'
import router from '@/router'
import '@core-scss/template/index.scss'
import '@styles/styles.scss'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { createApp } from 'vue'

import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import dateFormatter from './plugins/dateFormatter'

// Mixins
import authMixin from "@/mixins/authMixin"
import utilityMixin from "@/mixins/utilityMixin"
import moment from "moment"



const googleMapApi = import.meta.env.VITE_GOOGLE_MAP_API


const script = document.createElement('script')

script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapApi}&libraries=places`
script.defer = true
script.async = true

document.head.appendChild(script)

loadFonts()

const toast_options = {}

// Create vue app
const app = createApp(App)

app.config.globalProperties.moment = moment

//app.prototype.$attachment_path = "http://127.0.0.1:8000/attachments/";
app.config.globalProperties.$attachment_path = "http://127.0.0.1:8000/attachments/"

// pinia
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

// Register Mixins
app.mixin(utilityMixin)
app.mixin(authMixin)

// Use plugins
app.use(dateFormatter)
app.use(vuetify)
app.use(pinia)
app.use(router)
app.use(layoutsPlugin)
app.component('VueDatePicker', VueDatePicker)
app.use(Vue3Toasity, toast_options)
app.use(ToastPlugin, {
    position: 'top-right'
})
app.use(LoadingPlugin)

app.component('QuillEditor', QuillEditor)


// Mount vue app
app.mount('#app')

export { app }
