import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import './app.css'
import App from './App.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: () => import('./pages/Home.vue') },
    { path: '/tours', component: () => import('./pages/Catalog.vue') },
    { path: '/tours/:id', component: () => import('./pages/TourDetail.vue') },
    { path: '/admin/login', component: () => import('./pages/admin/Login.vue') },
    { path: '/admin', component: () => import('./pages/admin/Dashboard.vue'), children: [
      { path: '', component: () => import('./pages/admin/Index.vue') },
      { path: 'tours', component: () => import('./pages/admin/Tours.vue') },
      { path: 'categories', component: () => import('./pages/admin/Categories.vue') },
    ]},
  ]
})

const app = createApp(App)
app.use(router)
app.mount('#app')