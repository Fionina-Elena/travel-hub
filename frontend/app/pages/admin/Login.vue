<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon">🔐</div>
        <h1 class="login-title">Вход в панель управления</h1>
        <p class="login-subtitle">Управление каталогом туров</p>
      </div>

      <div v-if="error" class="error">
        {{ error }}
      </div>

      <form @submit.prevent="handleLogin" class="form">
        <div class="form-group">
          <label class="label">Email</label>
          <input v-model="form.email" type="email" placeholder="admin@example.com" class="input" required />
        </div>
        <div class="form-group">
          <label class="label">Пароль</label>
          <input v-model="form.password" type="password" placeholder="••••••••" class="input" required />
        </div>
        <button type="submit" class="btn" :disabled="loading">
          {{ loading ? 'Вход...' : 'Войти' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../../api'

const router = useRouter()
const form = ref({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await auth.login(form.value.email, form.value.password)
    localStorage.setItem('admin_token', response.data.token)
    router.push('/admin')
  } catch (e: any) {
    error.value = e.response?.data?.errors?.email?.[0] || 'Неверные учетные данные'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  padding: 1rem;
}

.login-card {
  background: white;
  border-radius: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  padding: 2rem;
  width: 100%;
  max-width: 28rem;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.login-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.login-subtitle {
  color: #6b7280;
  margin-top: 0.5rem;
}

.error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
  padding: 1rem;
  border-radius: 0.75rem;
  margin-bottom: 1.5rem;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.75rem;
  font-size: 1rem;
  transition: all 0.2s;
}

.input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.5);
}

.btn {
  width: 100%;
  background: #2563eb;
  color: white;
  padding: 0.75rem;
  border-radius: 0.75rem;
  font-weight: 700;
  transition: background 0.2s;
  border: none;
  cursor: pointer;
}

.btn:hover {
  background: #1d4ed8;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>