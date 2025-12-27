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
import Schedule from './views/Schedule.vue'

// Admin views
import AdminDashboard from './views/admin/AdminDashboard.vue'
import AdminUsers from './views/admin/Users.vue'
import AdminDskp from './views/admin/Dskp.vue'

// Auth views
import VerifyEmail from './views/VerifyEmail.vue'
import ResetPassword from './views/ResetPassword.vue'
import EmailVerificationRequired from './views/EmailVerificationRequired.vue'

// Router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'Login', component: Login },
    { path: '/verify-email', name: 'VerifyEmail', component: VerifyEmail },
    { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },
    { path: '/email-verification-required', name: 'EmailVerificationRequired', component: EmailVerificationRequired },
    { path: '/dashboard', name: 'Dashboard', component: Dashboard, meta: { auth: true, verified: true } },
    { path: '/class/:id', name: 'ClassDetail', component: ClassDetail, meta: { auth: true } },
    { path: '/class/:classId/progress', name: 'Progress', component: Progress, meta: { auth: true } },
    { path: '/class/:classId/student/:studentId', name: 'StudentDetail', component: StudentDetail, meta: { auth: true } },
    { path: '/tp-input', name: 'TpInput', component: TpInput, meta: { auth: true } },
    { path: '/profile', name: 'Profile', component: Profile, meta: { auth: true } },
    { path: '/schedule', name: 'Schedule', component: Schedule, meta: { auth: true } },
    // Admin routes
    { path: '/admin', name: 'AdminDashboard', component: AdminDashboard, meta: { auth: true, admin: true } },
    { path: '/admin/users', name: 'AdminUsers', component: AdminUsers, meta: { auth: true, admin: true } },
    { path: '/admin/dskp', name: 'AdminDskp', component: AdminDskp, meta: { auth: true, admin: true } },
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const isVerified = user.email_verified_at !== null && user.email_verified_at !== undefined
  
  // Check if route requires auth
  if (to.meta.auth && !token) {
    next('/login')
  } 
  // Check if user needs email verification (only for non-admin protected routes)
  else if (to.meta.auth && token && !isVerified && user.role !== 'admin' && to.path !== '/email-verification-required') {
    next('/email-verification-required')
  }
  // Admin check
  else if (to.meta.admin && user.role !== 'admin') {
    next('/dashboard')
  } 
  // Already logged in, redirect from login
  else if (to.path === '/login' && token) {
    if (!isVerified && user.role !== 'admin') {
      next('/email-verification-required')
    } else {
      next(user.role === 'admin' ? '/admin' : '/dashboard')
    }
  } 
  // Admin shouldn't see teacher dashboard
  else if (to.path === '/dashboard' && user.role === 'admin') {
    next('/admin')
  }
  // Verified user trying to access verification page
  else if (to.path === '/email-verification-required' && isVerified) {
    next('/dashboard')
  }
  else {
    next()
  }
})

const app = createApp(App)
app.use(router)
app.mount('#app')

