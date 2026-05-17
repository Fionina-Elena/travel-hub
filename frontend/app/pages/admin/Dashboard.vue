<template>
  <div class="admin-page">
    <header class="header">
      <div class="header-container">
        <div class="header-left">
          <a href="/" class="logo">
            <span class="logo-icon">🌍</span>
            <span class="logo-text">Travel-Hub</span>
          </a>
          <nav class="nav">
            <router-link to="/admin" class="nav-link" :class="{ active: route.path === '/admin' }">Главная</router-link>
            <router-link to="/admin/tours" class="nav-link" :class="{ active: route.path.includes('/tours') }">Туры</router-link>
            <router-link to="/admin/categories" class="nav-link" :class="{ active: route.path.includes('/categories') }">Категории</router-link>
          </nav>
        </div>
        <div class="header-right">
          <div class="user-info">
            <span class="user-icon">👤</span>
            <span class="user-name">{{ user?.name }}</span>
          </div>
          <button @click="logout" class="logout-btn">
            <span>🚪</span>
            <span>Выйти</span>
          </button>
        </div>
      </div>
    </header>
    
    <main class="main">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { auth } from '../../api'

const route = useRoute()
const router = useRouter()
const user = ref<any>(null)

onMounted(async () => {
  try {
    const response = await auth.me()
    user.value = response.data
  } catch {
    router.push('/admin/login')
  }
})

const logout = () => {
  localStorage.removeItem('admin_token')
  router.push('/admin/login')
}
</script>

<style scoped>
.admin-page {
  min-height: 100vh;
  background: #f3f4f6;
}

.header {
  background: white;
  box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
  border-bottom: 1px solid #e5e7eb;
}

.header-container {
  max-width: 80rem;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 4rem;
}

.header-inner {
  display: flex;
  justify-content: space-between;
  height: 4rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-shrink: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #1f2937;
}

.logo:hover {
  color: #2563eb;
}

.logo-icon {
  font-size: 1.5rem;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
}

.nav {
  display: none;
  align-items: center;
  gap: 0.25rem;
}

@media (min-width: 768px) {
  .nav {
    display: flex;
  }
}

.nav-link {
  padding: 0.5rem 1rem;
  color: #4b5563;
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.nav-link:hover {
  color: #2563eb;
  background: #eff6ff;
}

.nav-link.active {
  background: #eff6ff;
  color: #2563eb;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.75rem;
  background: #f9fafb;
  border-radius: 0.5rem;
}

.user-icon {
  color: #6b7280;
}

.user-name {
  color: #374151;
  font-weight: 500;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #6b7280;
  transition: color 0.2s;
  background: none;
  border: none;
  cursor: pointer;
}

.logout-btn:hover {
  color: #dc2626;
}

.main {
  max-width: 80rem;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>