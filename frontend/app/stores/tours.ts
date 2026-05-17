import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToursStore = defineStore('tours', () => {
  const tours = ref<any[]>([])
  const currentTour = ref<any>(null)
  const loading = ref(false)

  const fetchTours = async (params?: object) => {
    loading.value = true
    try {
      const { tours: toursApi } = await import('../app/api')
      const response = await toursApi.getAll(params)
      tours.value = response.data.data || response.data
    } finally {
      loading.value = false
    }
  }

  const fetchTour = async (id: number) => {
    loading.value = true
    try {
      const { tours: toursApi } = await import('../app/api')
      const response = await toursApi.getOne(id)
      currentTour.value = response.data
    } finally {
      loading.value = false
    }
  }

  const createTour = async (data: any) => {
    const { tours: toursApi } = await import('../app/api')
    const response = await toursApi.create(data)
    tours.value.push(response.data)
    return response.data
  }

  const updateTour = async (id: number, data: any) => {
    const { tours: toursApi } = await import('../app/api')
    const response = await toursApi.update(id, data)
    const index = tours.value.findIndex(t => t.id === id)
    if (index !== -1) {
      tours.value[index] = response.data
    }
    return response.data
  }

  const deleteTour = async (id: number) => {
    const { tours: toursApi } = await import('../app/api')
    await toursApi.delete(id)
    tours.value = tours.value.filter(t => t.id !== id)
  }

  return { tours, currentTour, loading, fetchTours, fetchTour, createTour, updateTour, deleteTour }
})