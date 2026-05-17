import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api/v1',
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('admin_token')
  if (token) {
    config.headers['X-Admin-Token'] = token
  }
  return config
})

export const auth = {
  login: (email: string, password: string) => api.post('/auth/login', { email, password }),
  me: () => api.get('/auth/me'),
}

export const categories = {
  getAll: () => api.get('/categories'),
  create: (data: any) => api.post('/categories', data),
  update: (id: number, data: any) => api.put(`/categories/${id}`, data),
  delete: (id: number) => api.delete(`/categories/${id}`),
}

export const tours = {
  getAll: (params?: any) => api.get('/tours', { params }),
  getOne: (id: number) => api.get(`/tours/${id}`),
  create: (data: any) => api.post('/tours', data),
  update: (id: number, data: any) => api.put(`/tours/${id}`, data),
  delete: (id: number) => api.delete(`/tours/${id}`),
  search: (q: string) => api.get('/tours/search', { params: { q } }),
  addImages: (id: number, images: any[]) => api.post(`/tours/${id}/images`, { images }),
}

export const tourDates = {
  create: (data: any) => api.post('/tour-dates', data),
  update: (id: number, data: any) => api.put(`/tour-dates/${id}`, data),
  delete: (id: number) => api.delete(`/tour-dates/${id}`),
}

export const images = {
  upload: (file: File) => {
    const formData = new FormData()
    formData.append('file', file)
    return axios.post('http://127.0.0.1:8000/api/v1/images/upload', formData, {
      headers: { 
        'Content-Type': 'multipart/form-data',
        'X-Admin-Token': localStorage.getItem('admin_token') || ''
      }
    })
  },
}

export default api