<template>
  <div class="app-layout">
    <header v-if="!isAdminRoute" class="app-header">
      <div class="header-container">
        <a href="/" class="logo">
          <span class="logo-icon">🌍</span>
          <span class="logo-text">Travel-Hub</span>
        </a>
        <nav class="nav-menu">
          <router-link to="/tours" class="nav-link">Каталог</router-link>
        </nav>
        <div class="user-menu">
          <template v-if="isLoggedIn">
            <router-link to="/admin" class="user-info">
              <span>👤</span>
              <span>Администратор</span>
            </router-link>
            <button @click="logout" class="logout-btn">🚪 Выйти</button>
          </template>
          <router-link v-else to="/admin/login" class="nav-link">Войти</router-link>
        </div>
      </div>
    </header>

    <main class="app-main">
      <router-view />
    </main>

    <footer v-if="!isAdminRoute" class="app-footer">
      <div class="footer-container">
        <p class="footer-text">© 2026 Travel-Hub — Каталог туров по всему миру</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const isAdminRoute = computed(() => route.path.startsWith('/admin'))
const isLoggedIn = computed(() => !!localStorage.getItem('admin_token'))

const logout = () => {
  localStorage.removeItem('admin_token')
  router.push('/')
}
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.app-header {
  background: white;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 40;
}

.header-container {
  max-width: 80rem;
  margin: 0 auto;
  padding: 0 1rem;
  height: 4rem;
  display: flex;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.logo-icon {
  font-size: 1.5rem;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2563eb;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-left: 1.5rem;
  flex: 1;
}

.nav-link {
  padding: 0.5rem 1rem;
  color: #4b5563;
  border-radius: 0.5rem;
  transition: all 0.2s;
  font-weight: 500;
}

.nav-link:hover {
  color: #2563eb;
  background: #f9fafb;
}

.nav-link.router-link-active {
  color: #2563eb;
  background: #eff6ff;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #374151;
  font-weight: 500;
}

.logout-btn {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  font-weight: 500;
  transition: color 0.2s;
}

.logout-btn:hover {
  color: #ef4444;
}

.app-main {
  flex: 1;
}

.app-footer {
  background: #1f2937;
  color: white;
  margin-top: auto;
}

.footer-container {
  max-width: 80rem;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.footer-text {
  text-align: center;
  color: #9ca3af;
}
</style>