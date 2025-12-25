import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './style.css'

// Import views
import Login from './views/Login.vue'
import Dashboard from './views/Dashboard.vue'
import ClassDetail from './views/ClassDetail.vue'
import TpInput from './views/TpInput.vue'
import Progress from './views/Progress.vue'
import StudentDetail from './views/StudentDetail.vue'
import Profile from './views/Profile.vue'

// Router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'Login', component: Login },
    { path: '/dashboard', name: 'Dashboard', component: Dashboard, meta: { auth: true } },
    { path: '/class/:id', name: 'ClassDetail', component: ClassDetail, meta: { auth: true } },
    { path: '/class/:classId/progress', name: 'Progress', component: Progress, meta: { auth: true } },
    { path: '/class/:classId/student/:studentId', name: 'StudentDetail', component: StudentDetail, meta: { auth: true } },
    { path: '/tp-input', name: 'TpInput', component: TpInput, meta: { auth: true } },
    { path: '/profile', name: 'Profile', component: Profile, meta: { auth: true } },
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  if (to.meta.auth && !token) {
    next('/login')
  } else if (to.path === '/login' && token) {
    next('/dashboard')
  } else {
    next()
  }
})

const app = createApp(App)
app.use(router)
app.mount('#app')
