<template>
  <div class="page">
    <div class="container">
      <h1 class="title">Каталог туров</h1>

      <div class="filters">
        <div class="filter-group">
          <span class="search-icon">🔍</span>
          <input
            v-model="filters.query"
            @keyup.enter="fetchTours"
            type="text"
            placeholder="Поиск туров..."
            class="input"
          />
        </div>
        
        <select v-model="filters.category" @change="fetchTours" class="input">
          <option value="">Все категории</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>

        <input
          v-model="filters.min_days"
          @change="fetchTours"
          type="number"
          placeholder="Мин. дней"
          class="input"
        />

        <input
          v-model="filters.max_days"
          @change="fetchTours"
          type="number"
          placeholder="Макс. дней"
          class="input"
        />

        <button @click="fetchTours" class="btn">Найти</button>
      </div>

      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p class="loading-text">Загрузка туров...</p>
      </div>
      
      <div v-else-if="tours.length === 0" class="empty">
        <div class="empty-icon">🔍</div>
        <h3 class="empty-title">Туры не найдены</h3>
        <p class="empty-text">Попробуйте изменить параметры поиска</p>
      </div>
      
      <div v-else class="grid-3">
        <router-link
          v-for="tour in tours"
          :key="tour.id"
          :to="`/tours/${tour.id}`"
          class="tour-card"
        >
          <div class="tour-image">
            <img v-if="tour.images?.[0]" :src="tour.images[0].url" :alt="tour.images[0].alt" class="tour-img" />
            <div v-else class="tour-placeholder">🌍</div>
          </div>
          <div class="tour-content">
            <span class="category-badge">{{ tour.category?.name }}</span>
            <h3 class="tour-title">{{ tour.title }}</h3>
            <p class="tour-description">{{ tour.description }}</p>
            <div class="tour-footer">
              <span class="tour-duration">📅 {{ tour.duration_days }} дней</span>
              <span v-if="tour.dates?.[0]" class="tour-price">от {{ tour.dates[0].price.toLocaleString() }} ₽</span>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { tours as toursApi, categories as categoriesApi } from '../api'

const tours = ref<any[]>([])
const categories = ref<any[]>([])
const loading = ref(true)
const filters = ref({ category: '', min_days: '', max_days: '', query: '' })

const fetchTours = async () => {
  loading.value = true
  const params: any = {}
  if (filters.value.category) params.category = filters.value.category
  if (filters.value.min_days) params.min_days = filters.value.min_days
  if (filters.value.max_days) params.max_days = filters.value.max_days
  
  try {
    const response = filters.value.query 
      ? await toursApi.search(filters.value.query)
      : await toursApi.getAll(params)
    tours.value = response.data.data || response.data
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const catResponse = await categoriesApi.getAll()
  categories.value = catResponse.data
  await fetchTours()
})
</script>

<style scoped>
.page {
  background: #f9fafb;
  min-height: 100vh;
  padding: 2rem 0;
}

.container {
  max-width: 80rem;
  margin: 0 auto;
  padding: 0 1rem;
}

.title {
  font-size: 1.875rem;
  font-weight: 700;
  text-align: center;
  margin-bottom: 2rem;
}

.filters {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  padding: 1.5rem;
  margin-bottom: 2rem;
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  align-items: center;
}

@media (min-width: 768px) {
  .filters {
    grid-template-columns: repeat(5, 1fr);
  }
}

.filter-group {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.input {
  width: 100%;
  padding: 0.75rem 1rem;
  padding-left: 2.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  background: white;
  transition: all 0.2s;
}

.input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.5);
}

select.input {
  padding-left: 1rem;
}

.btn {
  background: #2563eb;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: background 0.2s;
  width: 100%;
}

.btn:hover {
  background: #1d4ed8;
}

.loading {
  text-align: center;
  padding: 5rem 0;
}

.spinner {
  display: inline-block;
  width: 3rem;
  height: 3rem;
  border: 4px solid #2563eb;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-text {
  margin-top: 1rem;
  color: #6b7280;
}

.empty {
  text-align: center;
  padding: 5rem 0;
  background: white;
  border-radius: 1rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-text {
  color: #6b7280;
}

.grid-3 {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 2rem;
}

@media (min-width: 768px) {
  .grid-3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .grid-3 {
    grid-template-columns: repeat(3, 1fr);
  }
}

.tour-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: box-shadow 0.3s;
  display: block;
}

.tour-card:hover {
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}

.tour-image {
  height: 14rem;
  background: linear-gradient(135deg, #e5e7eb, #d1d5db);
  overflow: hidden;
}

.tour-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}

.tour-card:hover .tour-img {
  transform: scale(1.05);
}

.tour-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  font-size: 3rem;
}

.tour-content {
  padding: 1.5rem;
}

.category-badge {
  display: inline-block;
  background: #dbeafe;
  color: #2563eb;
  font-size: 0.875rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  margin-bottom: 0.75rem;
}

.tour-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  transition: color 0.2s;
}

.tour-card:hover .tour-title {
  color: #2563eb;
}

.tour-description {
  color: #4b5563;
  font-size: 0.875rem;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.tour-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #f3f4f6;
}

.tour-duration {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #6b7280;
}

.tour-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2563eb;
}
</style>