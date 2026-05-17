<template>
  <div class="page">
    <div class="container">
      <router-link to="/tours" class="back-link">← Назад к каталогу</router-link>

      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>
      
      <div v-else-if="!tour" class="empty">
        <div class="empty-icon">😕</div>
        <h3 class="empty-title">Тур не найден</h3>
      </div>
      
      <template v-else>
        <div class="content-grid">
          <div class="main-column">
            <div class="card">
              <div class="gallery">
                <img v-if="tour.images && tour.images[currentImageIndex]" 
                  :src="tour.images[currentImageIndex].url" 
                  :alt="tour.images[currentImageIndex].alt" 
                  class="gallery-main-img" 
                />
                <div v-else class="gallery-placeholder">🌍</div>
                
                <button v-if="tour.images && tour.images.length > 1" @click="prevImage" class="gallery-btn gallery-btn-prev">‹</button>
                <button v-if="tour.images && tour.images.length > 1" @click="nextImage" class="gallery-btn gallery-btn-next">›</button>
                
                <div v-if="tour.images && tour.images.length > 1" class="gallery-dots">
                  <button v-for="(_, idx) in tour.images" :key="idx" @click="currentImageIndex = idx" :class="['dot', idx === currentImageIndex ? 'dot-active' : '']" />
                </div>
                
                <div v-if="tour.images && tour.images.length > 1" class="gallery-counter">{{ currentImageIndex + 1 }} / {{ tour.images.length }}</div>
              </div>
              
              <div v-if="tour.images && tour.images.length > 1" class="gallery-thumbs">
                <img v-for="(img, idx) in tour.images" :key="img.id || idx" :src="img.url" :alt="img.alt" @click="currentImageIndex = idx" :class="['thumb', idx === currentImageIndex ? 'thumb-active' : '']" />
              </div>
              
              <div class="card-body">
                <span class="category-badge">{{ tour.category?.name }}</span>
                <h1 class="tour-title">{{ tour.title }}</h1>
                
                <div class="section">
                  <h2 class="section-title">📝 Описание</h2>
                  <p class="description">{{ tour.description }}</p>
                </div>

                <div v-if="tour.highlights && tour.highlights.length" class="section">
                  <h2 class="section-title">✨ Особенности</h2>
                  <div class="highlights">
                    <span v-for="(h, i) in tour.highlights" :key="i" class="highlight">{{ h }}</span>
                  </div>
                </div>

                <div class="info-grid">
                  <div v-if="tour.included" class="info-box info-box-green">
                    <h3 class="info-title">✅ Что включено</h3>
                    <p class="info-text">{{ tour.included }}</p>
                  </div>
                  <div v-if="tour.excluded" class="info-box info-box-red">
                    <h3 class="info-title">❌ Не включено</h3>
                    <p class="info-text">{{ tour.excluded }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="tour.route_points && tour.route_points.length" class="card">
              <h2 class="section-title">🗺️ Маршрут</h2>
              <div id="yandex-map" class="map"></div>
            </div>
          </div>

          <div class="sidebar">
            <div class="sidebar-card">
              <div class="duration">
                <span class="duration-icon">📅</span>
                <div>
                  <div class="duration-days">{{ tour.duration_days }}</div>
                  <div class="duration-label">дней</div>
                </div>
              </div>
              
              <h3 class="sidebar-title">💰 Доступные даты</h3>
              <div v-if="tour.dates && tour.dates.length" class="dates">
                <div v-for="date in tour.dates" :key="date.id" class="date-item">
                  <span class="date-text">{{ new Date(date.date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
                  <span class="date-price">{{ date.price.toLocaleString() }} ₽</span>
                </div>
              </div>
              <p v-else class="no-dates">Нет доступных дат</p>

              <div class="sidebar-footer">
                <p class="contact-text">Для бронирования свяжитесь с нами</p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { tours as toursApi } from '../api'

const route = useRoute()
const tour = ref<any>(null)
const loading = ref(true)
const currentImageIndex = ref(0)

const prevImage = () => {
  if (tour.value?.images?.length) {
    currentImageIndex.value = (currentImageIndex.value - 1 + tour.value.images.length) % tour.value.images.length
  }
}

const nextImage = () => {
  if (tour.value?.images?.length) {
    currentImageIndex.value = (currentImageIndex.value + 1) % tour.value.images.length
  }
}

const loadTour = async () => {
  loading.value = true
  currentImageIndex.value = 0
  try {
    const id = Number(route.params.id)
    const response = await toursApi.getOne(id)
    tour.value = response.data
  } finally {
    loading.value = false
  }
}

const initMap = () => {
  if (!tour.value?.route_points?.length) return

  const mapEl = document.getElementById('yandex-map')
  if (!mapEl) {
    setTimeout(initMap, 300)
    return
  }
  
  const ymapsLib = (window as any).ymaps
  if (!ymapsLib) {
    setTimeout(initMap, 200)
    return
  }
  
  const firstPoint = tour.value.route_points[0]
  const firstCoords = firstPoint?.coords
  const center = Array.isArray(firstCoords) ? firstCoords : [55.75, 37.62]
  const zoom = Array.isArray(firstCoords) ? 10 : 7
  
  const map = new ymapsLib.Map('yandex-map', {
    center: center,
    zoom: zoom,
    controls: ['zoomControl', 'fullscreenControl']
  })
  
  tour.value.route_points.forEach((point: any, index: number) => {
    const coords = point.coords
    
    if (Array.isArray(coords)) {
      const placemark = new ymapsLib.Placemark(coords, {
        iconCaption: point.name,
        preset: index === 0 ? 'islands#greenIcon' : 'islands#blueIcon'
      })
      map.geoObjects.add(placemark)
    } else {
      ymapsLib.geocode(coords).then((res: any) => {
        const geoObject = res.geoObjects.get(0)
        if (geoObject) {
          const placemark = new ymapsLib.Placemark(
            geoObject.geometry.getCoordinates(),
            { iconCaption: point.name, preset: index === 0 ? 'islands#greenIcon' : 'islands#blueIcon' }
          )
          map.geoObjects.add(placemark)
        }
      })
    }
  })
}

const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'ArrowLeft') prevImage()
  if (e.key === 'ArrowRight') nextImage()
}

onMounted(() => {
  loadTour()
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})

watch(() => tour.value?.route_points, (points) => {
  if (points?.length) {
    setTimeout(initMap, 300)
  }
}, { immediate: true })
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

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #2563eb;
  margin-bottom: 1.5rem;
}

.back-link:hover {
  color: #1d4ed8;
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
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
}

@media (min-width: 1024px) {
  .content-grid {
    grid-template-columns: 2fr 1fr;
  }
}

.main-column {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  overflow: hidden;
}

.gallery {
  position: relative;
  height: 20rem;
  background: linear-gradient(135deg, #e5e7eb, #d1d5db);
}

.gallery-main-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: opacity 0.3s;
}

.gallery-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  font-size: 5rem;
}

.gallery-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 3rem;
  height: 3rem;
  background: rgba(255,255,255,0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  transition: all 0.2s;
  border: none;
  cursor: pointer;
  color: #374151;
  font-size: 1.5rem;
}

.gallery-btn:hover {
  background: white;
  color: #111827;
}

.gallery-btn-prev {
  left: 1rem;
}

.gallery-btn-next {
  right: 1rem;
}

.gallery-dots {
  position: absolute;
  bottom: 1rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.5rem;
}

.dot {
  width: 0.75rem;
  height: 0.75rem;
  border-radius: 50%;
  background: rgba(255,255,255,0.5);
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.dot:hover {
  background: rgba(255,255,255,0.75);
}

.dot-active {
  background: white;
  transform: scale(1.25);
}

.gallery-counter {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(0,0,0,0.5);
  color: white;
  font-size: 0.875rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
}

.gallery-thumbs {
  padding: 1rem;
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
}

.thumb {
  width: 5rem;
  height: 4rem;
  object-fit: cover;
  border-radius: 0.5rem;
  cursor: pointer;
  opacity: 0.6;
  transition: all 0.2s;
}

.thumb:hover {
  opacity: 1;
}

.thumb-active {
  opacity: 1;
  box-shadow: 0 0 0 2px #2563eb;
}

.card-body {
  padding: 2rem;
}

.category-badge {
  display: inline-block;
  background: #dbeafe;
  color: #2563eb;
  font-size: 0.875rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  margin-bottom: 1rem;
}

.tour-title {
  font-size: 1.875rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.section {
  margin-top: 2rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.description {
  color: #374151;
  font-size: 1.125rem;
  line-height: 1.75;
}

.highlights {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.highlight {
  background: #fef3c7;
  color: #b45309;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  margin-top: 2rem;
}

@media (min-width: 768px) {
  .info-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.info-box {
  border-radius: 0.75rem;
  padding: 1.25rem;
}

.info-box-green {
  background: #ecfdf5;
}

.info-box-red {
  background: #fef2f2;
}

.info-title {
  font-weight: 700;
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-box-green .info-title {
  color: #047857;
}

.info-box-red .info-title {
  color: #b91c1c;
}

.info-text {
  color: #374151;
}

.map {
  width: 100%;
  height: 24rem;
  border-radius: 0.75rem;
}

.sidebar {
  position: sticky;
  top: 6rem;
}

.sidebar-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  padding: 1.5rem;
}

.duration {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.duration-icon {
  font-size: 2.5rem;
}

.duration-days {
  font-size: 2rem;
  font-weight: 700;
  color: #2563eb;
}

.duration-label {
  color: #6b7280;
}

.sidebar-title {
  font-weight: 700;
  font-size: 1.125rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dates {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.date-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 0.75rem;
}

.date-text {
  font-weight: 500;
}

.date-price {
  font-weight: 700;
  color: #2563eb;
  font-size: 1.125rem;
}

.no-dates {
  color: #6b7280;
  text-align: center;
  padding: 1rem 0;
}

.sidebar-footer {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f3f4f6;
}

.contact-text {
  font-size: 0.875rem;
  color: #6b7280;
  text-align: center;
}
</style>