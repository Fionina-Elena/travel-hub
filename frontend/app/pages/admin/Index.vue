<template>
  <div class="dashboard">
    <h1 class="title">Панель управления</h1>
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.tours }}</div>
          <div class="stat-label">Всего туров</div>
        </div>
      </div>
      <div class="stat-card stat-card-green">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <div class="stat-value stat-value-green">{{ stats.published }}</div>
          <div class="stat-label">Опубликовано</div>
        </div>
      </div>
      <div class="stat-card stat-card-purple">
        <div class="stat-icon">📁</div>
        <div class="stat-info">
          <div class="stat-value stat-value-purple">{{ stats.categories }}</div>
          <div class="stat-label">Категорий</div>
        </div>
      </div>
    </div>

    <div class="generator-grid">
      <AIGenerator @tour-generated="handleTourGenerated" />
    </div>

    <div v-if="generatedTour" class="modal-overlay" @click.self="generatedTour = null">
      <div class="modal">
        <h2 class="modal-title">✨ Сгенерированный тур</h2>
        <div class="generated-data">
          <div><strong>Название:</strong> {{ generatedTour.title }}</div>
          <div><strong>Описание:</strong> {{ generatedTour.description }}</div>
          <div><strong>Дней:</strong> {{ generatedTour.duration_days }}</div>
          <div v-if="generatedTour.highlights"><strong>Особенности:</strong>
            <ul class="highlights-list">
              <li v-for="(h, i) in generatedTour.highlights" :key="i">{{ h }}</li>
            </ul>
          </div>
        </div>
        <div class="modal-actions">
          <button @click="generatedTour = null" class="btn-cancel">Отмена</button>
          <button @click="saveGenerated" class="btn-save">Сохранить</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AIGenerator from './AIGenerator.vue'
import { tours as toursApi, categories as categoriesApi } from '../../api'

const stats = ref({ tours: 0, published: 0, categories: 0 })
const generatedTour = ref<any>(null)

const loadStats = async () => {
  const [toursRes, catsRes] = await Promise.all([
    toursApi.getAll(),
    categoriesApi.getAll()
  ])
  const allTours = toursRes.data.data || toursRes.data
  stats.value = {
    tours: allTours.length,
    published: allTours.filter((t: any) => t.is_published).length,
    categories: catsRes.data.length
  }
}

const handleTourGenerated = (data: any) => {
  generatedTour.value = data
}

const saveGenerated = async () => {
  try {
    const response = await toursApi.create(generatedTour.value)
    console.log('Saved:', response)
    generatedTour.value = null
    loadStats()
    alert('Тур успешно сохранён!')
  } catch (error: any) {
    console.error('Save error:', error)
    alert('Ошибка: ' + (error.response?.data?.message || error.message))
  }
}

onMounted(loadStats)
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.stat-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  background: #dbeafe;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.stat-card-green .stat-icon {
  background: #d1fae5;
}

.stat-card-purple .stat-icon {
  background: #ede9fe;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.stat-value-green {
  color: #059669;
}

.stat-value-purple {
  color: #7c3aed;
}

.stat-label {
  color: #6b7280;
}

.generator-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 1024px) {
  .generator-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  width: 100%;
  max-width: 32rem;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.generated-data {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.highlights-list {
  list-style: disc;
  padding-left: 1.25rem;
  margin-top: 0.25rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.btn-cancel {
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background: white;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #f9fafb;
}

.btn-save {
  padding: 0.5rem 1rem;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
}

.btn-save:hover {
  background: #1d4ed8;
}
</style>