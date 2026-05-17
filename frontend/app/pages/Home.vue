<template>
  <div class="page">
    <div class="hero">
      <div class="hero-bg" style="background-image: url('/picture/home.jpeg');">
        <div class="hero-overlay"></div>
        <div class="hero-blur"></div>
        <div class="hero-gradient"></div>
      </div>
      <div class="hero-content">
        <h1 class="hero-title">Найдите идеальное путешествие</h1>
        <p class="hero-text">Откройте мир с нашим каталогом туров. Пляжный отдых, горные походы, экскурсии — всё в одном месте.</p>
        <router-link to="/tours" class="hero-btn">Смотреть туры →</router-link>
      </div>
    </div>

    <div class="section">
      <h2 class="section-title">Популярные направления</h2>
      <div class="grid-3">
        <div class="card">
          <div class="card-icon">🏖️</div>
          <h3 class="card-title">Пляжный отдых</h3>
          <p class="card-text">Солнце, море и песок. Лучшие курорты для вашего идеального отдыха.</p>
        </div>
        <div class="card">
          <div class="card-icon">🏔️</div>
          <h3 class="card-title">Горный туризм</h3>
          <p class="card-text">Незабываемые виды и приключения. Покоряйте вершины вместе с нами.</p>
        </div>
        <div class="card">
          <div class="card-icon">🏛️</div>
          <h3 class="card-title">Экскурсии</h3>
          <p class="card-text">Исторические места, культурные памятники и интересные маршруты.</p>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Туры</h2>
        <router-link to="/tours" class="link">Все туры →</router-link>
      </div>
      
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
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
            
            <div v-if="tour.route_points?.length" class="tour-badge">
              <span>🗺️</span> {{ tour.route_points.length }} точек маршрута
            </div>
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
import { tours as toursApi } from '../api'

const tours = ref<any[]>([])
const loading = ref(true)

const fetchTours = async () => {
  loading.value = true
  try {
    const response = await toursApi.getAll({ published_only: true })
    tours.value = (response.data.data || response.data).slice(0, 6)
  } finally {
    loading.value = false
  }
}

onMounted(fetchTours)
</script>

<style scoped>
.page {
  position: relative;
}

.hero {
  position: relative;
  min-height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background-color: #2563eb;
  opacity: 0.1;
}

.hero-blur {
  position: absolute;
  inset: 0;
  backdrop-filter: blur(4px);
}

.hero-gradient {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4rem;
  background: linear-gradient(to bottom, transparent, white);
}

.hero-content {
  position: relative;
  z-index: 10;
  max-width: 80rem;
  margin: 0 auto;
  padding: 0 1rem;
  text-align: center;
}

.hero-title {
  font-size: 2.25rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: white;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.hero-text {
  font-size: 1.25rem;
  color: white;
  margin-bottom: 2rem;
  max-width: 36rem;
  margin-left: auto;
  margin-right: auto;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.hero-btn {
  display: inline-block;
  background: white;
  color: #2563eb;
  padding: 1rem 2rem;
  border-radius: 9999px;
  font-weight: 700;
  font-size: 1.125rem;
  transition: all 0.2s;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}

.hero-btn:hover {
  background: #eff6ff;
}

.section {
  max-width: 80rem;
  margin: 0 auto;
  padding: 4rem 1rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  text-align: center;
  margin-bottom: 3rem;
}

.section-header .section-title {
  margin-bottom: 0;
  text-align: left;
}

.grid-3 {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 2rem;
}

@media (min-width: 768px) {
  .grid-3 {
    grid-template-columns: repeat(3, 1fr);
  }
}

.card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  padding: 1.5rem;
  transition: box-shadow 0.3s;
}

.card:hover {
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}

.card-icon {
  font-size: 2.25rem;
  margin-bottom: 1rem;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.card-text {
  color: #4b5563;
}

.link {
  color: #2563eb;
  font-weight: 500;
}

.link:hover {
  color: #1d4ed8;
}

.loading {
  text-align: center;
  padding: 3rem;
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

.tour-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: all 0.3s;
  display: block;
}

.tour-card:hover {
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}

.tour-image {
  height: 14rem;
  background: linear-gradient(135deg, #e5e7eb, #d1d5db);
  overflow: hidden;
  position: relative;
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

.tour-badge {
  position: absolute;
  bottom: 0.75rem;
  left: 0.75rem;
  background: rgba(0,0,0,0.6);
  color: white;
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  gap: 0.25rem;
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