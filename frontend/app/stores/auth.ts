import { createPinia } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = () => {
  const isAuthenticated = ref(false)
  const user = ref(null)

  const login = async (email: string, password: string) => {
    const { auth } = await import('../api')
    const response = await auth.login(email, password)
    localStorage.setItem('admin_token', response.data.token)
    user.value = response.data.user
    isAuthenticated.value = true
    return response.data
  }

  const logout = () => {
    localStorage.removeItem('admin_token')
    user.value = null
    isAuthenticated.value = false
  }

  const checkAuth = async () => {
    const token = localStorage.getItem('admin_token')
    if (!token) return false

    const { auth } = await import('../api')
    try {
      const response = await auth.me()
      user.value = response.data
      isAuthenticated.value = true
      return true
    } catch {
      logout()
      return false
    }
  }

  return { isAuthenticated, user, login, logout, checkAuth }
}