<template>
  <div class="page">
    <div class="header">
      <h1 class="title">Управление турами</h1>
      <button @click="openForm()" class="btn">+ Добавить тур</button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>

    <div v-else class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Фото</th>
            <th>Название</th>
            <th>Категория</th>
            <th>Дней</th>
            <th>Цена</th>
            <th>Статус</th>
            <th class="text-right">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tour in tours" :key="tour.id" class="row">
            <td>
              <div class="image-wrapper">
                <img v-if="tour.images?.[0]" :src="tour.images[0].url" class="image" />
              </div>
            </td>
            <td class="font-medium">{{ tour.title }}</td>
            <td class="text-gray-600">{{ tour.category?.name }}</td>
            <td class="text-gray-600">{{ tour.duration_days }}</td>
            <td class="text-gray-600">{{ tour.dates?.[0]?.price ? tour.dates[0].price.toLocaleString() + ' ₽' : '-' }}</td>
            <td>
              <span :class="tour.is_published ? 'status-published' : 'status-draft'">
                {{ tour.is_published ? 'Опубликован' : 'Черновик' }}
              </span>
            </td>
            <td class="text-right">
              <button @click="editTour(tour)" class="btn-action btn-edit">Редактировать</button>
              <button @click="deleteTour(tour.id)" class="btn-action btn-delete">Удалить</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">
            <span>{{ editingTour ? '✏️' : '' }}</span>
            {{ editingTour ? 'Редактирование тура' : 'Создание нового тура' }}
          </h2>
          <button @click="closeForm" class="close-btn">×</button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="saveTour" class="form">
            <div class="form-row">
              <div class="form-group">
                <label class="label">
                  <span class="required">*</span> Название тура
                </label>
                <input v-model="form.title" type="text" required placeholder="Введите название тура" class="input" />
              </div>
              <div class="form-group">
                <label class="label">
                  <span class="required">*</span> Категория
                </label>
                <select v-model="form.category_id" class="input">
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="label">
                <span class="required">*</span> Описание
              </label>
              <textarea v-model="form.description" rows="4" required placeholder="Подробно опишите тур..." class="textarea"></textarea>
            </div>

            <div class="info-section">
              <h3 class="section-title">📋 Основная информация</h3>
              <div class="form-row mb-4">
                <div class="form-group">
                  <label class="label-sm">Количество дней</label>
                  <input v-model.number="form.duration_days" type="number" min="1" required class="input" />
                </div>
                <div class="form-group">
                  <label class="label-sm">Даты тура</label>
                  <button @click="addDate" type="button" class="btn-add">+ Добавить даты</button>
                </div>
                <div class="form-group">
                  <label class="label-sm">Статус</label>
                  <div @click="form.is_published = !form.is_published" class="toggle">
                    <span class="toggle-track" :class="form.is_published ? 'bg-green-500' : 'bg-gray-300'">
                      <span class="toggle-thumb" :class="form.is_published ? 'translate-x-6' : 'translate-x-1'"></span>
                    </span>
                    <span :class="form.is_published ? 'text-green-600' : 'text-gray-500'">
                      {{ form.is_published ? 'Опубликован' : 'Черновик' }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div v-if="form.dates.length" class="dates-list">
                <div v-for="(date, idx) in form.dates" :key="idx" class="date-item">
                  <input v-model="date.date" type="date" class="input-sm" />
                  <input v-model.number="date.price" type="number" placeholder="Цена ₽" class="input-sm w-32" />
                  <label class="checkbox-label">
                    <input v-model="date.is_active" type="checkbox" class="checkbox" />
                    Активна
                  </label>
                  <button @click="removeDate(idx)" type="button" class="btn-remove">×</button>
                </div>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label class="label-sm">Что включено</label>
                  <textarea v-model="form.included" rows="2" placeholder="Завтраки, трансфер..." class="textarea-sm"></textarea>
                </div>
                <div class="form-group">
                  <label class="label-sm">Не включено</label>
                  <textarea v-model="form.excluded" rows="2" placeholder="Авиаперелет..." class="textarea-sm"></textarea>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="label">Особенности тура</label>
              <textarea v-model="highlightsText" rows="3" placeholder="Лучшие пляжи&#10;SPA-процедуры&#10;Морская рыбалка" class="textarea"></textarea>
              <p class="hint">Вводите каждую особенность с новой строки</p>
            </div>

            <div class="form-group">
              <label class="label">📷 Фотографии</label>
              <div class="images-list">
                <div v-for="(img, idx) in form.images" :key="idx" class="image-item">
                  <img :src="img.url" class="preview" />
                  <button @click="removeImage(idx)" type="button" class="btn-remove-image">×</button>
                </div>
              </div>
              <label class="upload-btn">
                <span class="text-xl">+</span>
                <span>Добавить фото</span>
                <input @change="handleImageUpload" type="file" accept="image/*" multiple class="hidden" />
              </label>
            </div>

            <div class="form-group">
              <label class="label">🗺️ Маршрут на карте</label>
              <div class="map-wrapper">
                <RouteMapEditor v-model="form.route_points" />
              </div>
            </div>

            <div class="form-actions">
              <button type="button" @click="closeForm" class="btn-cancel">Отмена</button>
              <button type="submit" class="btn-submit" :disabled="saving">
                <span v-if="saving" class="spinner-sm">⏳</span>
                {{ saving ? 'Сохранение...' : (editingTour ? 'Сохранить изменения' : 'Создать тур') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { tours as toursApi, categories as categoriesApi, images as imagesApi, tourDates } from '../../api'
import RouteMapEditor from '../../components/RouteMapEditor.vue'

const tours = ref<any[]>([])
const categories = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const editingTour = ref<any>(null)
const formModalKey = ref(0)

const form = ref({
  category_id: null as number | null,
  title: '',
  description: '',
  duration_days: 1,
  included: '',
  excluded: '',
  is_published: false,
  images: [] as any[],
  route_points: [] as any[],
  highlights: [] as string[],
  dates: [] as any[]
})

const addDate = () => {
  const nextMonth = new Date()
  nextMonth.setMonth(nextMonth.getMonth() + 1)
  form.value.dates.push({
    date: nextMonth.toISOString().split('T')[0],
    price: 0,
    is_active: true
  })
}

const removeDate = (idx: number) => {
  form.value.dates.splice(idx, 1)
}

const highlightsText = computed({
  get: () => form.value.highlights?.join('\n') || '',
  set: (val) => { form.value.highlights = val.split('\n').filter((s: string) => s.trim()) }
})

const openForm = () => {
  editingTour.value = null
  formModalKey.value++
  form.value = {
    category_id: categories.value[0]?.id || null,
    title: '',
    description: '',
    duration_days: 1,
    included: '',
    excluded: '',
    is_published: false,
    images: [],
    route_points: [],
    highlights: [],
    dates: []
  }
  showForm.value = true
}

const editTour = (tour: any) => {
  editingTour.value = tour
  formModalKey.value++
  form.value = {
    category_id: tour.category_id,
    title: tour.title,
    description: tour.description,
    duration_days: tour.duration_days,
    included: tour.included || '',
    excluded: tour.excluded || '',
    is_published: tour.is_published,
    images: [...(tour.images || [])],
    route_points: [...(tour.route_points || [])],
    highlights: [...(tour.highlights || [])],
    dates: (tour.dates || []).map((d: any) => ({
      id: d.id,
      date: d.date.split('T')[0],
      price: d.price,
      is_active: d.is_active
    }))
  }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editingTour.value = null
}

const handleImageUpload = async (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return

  try {
    const response = await imagesApi.upload(file)
    form.value.images.push({ url: response.data.url, alt: '' })
  } catch (err) {
    console.error('Upload failed', err)
    alert('Ошибка загрузки изображения')
  }
}

const removeImage = (idx: number) => {
  form.value.images.splice(idx, 1)
}

const saveTour = async () => {
  saving.value = true
  try {
    const data = {
      category_id: form.value.category_id,
      title: form.value.title,
      description: form.value.description,
      duration_days: form.value.duration_days,
      included: form.value.included,
      excluded: form.value.excluded,
      is_published: form.value.is_published,
      route_points: form.value.route_points,
      highlights: form.value.highlights,
      images: form.value.images.map((img: any) => ({ url: img.url, alt: img.alt || '' }))
    }

    if (editingTour.value) {
      await toursApi.update(editingTour.value.id, data)
      await syncDates(editingTour.value.id)
    } else {
      const response = await toursApi.create(data)
      await syncDates(response.data.id)
    }
    closeForm()
    fetchData()
  } finally {
    saving.value = false
  }
}

const syncDates = async (tourId: number) => {
  const existingDates = editingTour.value?.dates || []
  
  for (const date of form.value.dates) {
    if (date.id) {
      const existing = existingDates.find((d: any) => d.id === date.id)
      if (existing && (existing.date !== date.date || existing.price !== date.price || existing.is_active !== date.is_active)) {
        await tourDates.update(date.id, {
          date: date.date,
          price: date.price,
          is_active: date.is_active
        })
      }
    } else {
      await tourDates.create({
        tour_id: tourId,
        date: date.date,
        price: date.price,
        is_active: date.is_active
      })
    }
  }
  
  const savedIds = form.value.dates.filter((d: any) => d.id).map((d: any) => d.id)
  const deletedIds = existingDates.filter((d: any) => !savedIds.includes(d.id))
  for (const id of deletedIds) {
    await tourDates.delete(id)
  }
}

const deleteTour = async (id: number) => {
  if (!confirm('Удалить тур?')) return
  await toursApi.delete(id)
  fetchData()
}

const fetchData = async () => {
  loading.value = true
  const [toursRes, catsRes] = await Promise.all([
    toursApi.getAll(),
    categoriesApi.getAll()
  ])
  tours.value = toursRes.data.data || toursRes.data
  categories.value = catsRes.data
  loading.value = false
}

onMounted(fetchData)
</script>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.title {
  font-size: 1.5rem;
  font-weight: 700;
}

.btn {
  background: #2563eb;
  color: white;
  padding: 0.5rem 1.25rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  transition: background 0.2s;
  border: none;
  cursor: pointer;
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

.table-wrapper {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  overflow: hidden;
}

.table {
  width: 100%;
}

.table th {
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #f9fafb;
  border-bottom: 1px solid #f3f4f6;
}

.row:hover {
  background: #f9fafb;
}

.table td {
  padding: 1rem 1.5rem;
}

.image-wrapper {
  width: 4rem;
  height: 3rem;
  background: #f3f4f6;
  border-radius: 0.5rem;
  overflow: hidden;
}

.image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.font-medium {
  font-weight: 500;
}

.text-gray-600 {
  color: #4b5563;
}

.status-published {
  background: #d1fae5;
  color: #047857;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-draft {
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.text-right {
  text-align: right;
}

.btn-action {
  background: none;
  border: none;
  cursor: pointer;
  font-weight: 500;
  margin-right: 1rem;
  transition: color 0.2s;
}

.btn-edit {
  color: #2563eb;
}

.btn-edit:hover {
  color: #1d4ed8;
}

.btn-delete {
  color: #ef4444;
}

.btn-delete:hover {
  color: #dc2626;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  width: 100%;
  max-width: 64rem;
  max-height: 95vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  background: linear-gradient(135deg, #2563eb, #6366f1);
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.close-btn {
  color: white;
  font-size: 1.5rem;
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background 0.2s;
  background: none;
  border: none;
  cursor: pointer;
}

.close-btn:hover {
  background: rgba(255,255,255,0.2);
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .form-row {
    grid-template-columns: repeat(2, 1fr);
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.required {
  color: #ef4444;
}

.label-sm {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
}

.input,
.textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.75rem;
  font-size: 1rem;
  transition: all 0.2s;
}

.input:focus,
.textarea:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.5);
}

.textarea {
  resize: none;
}

.input-sm,
.textarea-sm {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.input-sm:focus,
.textarea-sm:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.5);
}

.textarea-sm {
  resize: none;
}

.hint {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.info-section {
  background: #f9fafb;
  border-radius: 0.75rem;
  padding: 1rem;
}

.section-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.toggle {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: background 0.2s;
}

.toggle:hover {
  background: #f9fafb;
}

.toggle-track {
  position: relative;
  width: 2.75rem;
  height: 1.5rem;
  border-radius: 9999px;
  transition: background 0.2s;
}

.toggle-thumb {
  position: absolute;
  top: 0.25rem;
  width: 1rem;
  height: 1rem;
  background: white;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.translate-x-1 {
  transform: translateX(0.25rem);
}

.translate-x-6 {
  transform: translateX(1.375rem);
}

.toggle span:last-child {
  font-weight: 500;
}

.text-green-600 {
  color: #059669;
}

.text-gray-500 {
  color: #6b7280;
}

.dates-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.date-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: white;
  padding: 0.75rem;
  border-radius: 0.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #4b5563;
}

.checkbox {
  border-radius: 0.25rem;
}

.btn-remove {
  color: #ef4444;
  font-weight: 500;
  margin-left: auto;
  background: none;
  border: none;
  cursor: pointer;
}

.btn-remove:hover {
  color: #dc2626;
}

.btn-add {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background: white;
  color: #2563eb;
  font-weight: 500;
  text-align: left;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-add:hover {
  background: #f9fafb;
}

.images-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.image-item {
  position: relative;
}

.preview {
  width: 7rem;
  height: 6rem;
  object-fit: cover;
  border-radius: 0.75rem;
  box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
}

.btn-remove-image {
  position: absolute;
  top: -0.5rem;
  right: -0.5rem;
  width: 1.5rem;
  height: 1.5rem;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
  border: none;
  cursor: pointer;
}

.image-item:hover .btn-remove-image {
  opacity: 1;
}

.upload-btn {
  cursor: pointer;
  background: #eff6ff;
  border: 2px dashed #93c5fd;
  padding: 0.75rem 1.25rem;
  border-radius: 0.75rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #2563eb;
  font-weight: 500;
  transition: all 0.2s;
}

.upload-btn:hover {
  background: #dbeafe;
  border-color: #60a5fa;
}

.map-wrapper {
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  overflow: hidden;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
  margin: 0 -1.5rem -1.5rem;
  padding: 1rem 1.5rem;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  border: 2px solid #d1d5db;
  border-radius: 0.75rem;
  background: white;
  font-weight: 500;
  color: #4b5563;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-cancel:hover {
  background: #f9fafb;
}

.btn-submit {
  padding: 0.75rem 2rem;
  background: linear-gradient(135deg, #2563eb, #2563eb);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 500;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  cursor: pointer;
}

.btn-submit:hover {
  background: linear-gradient(135deg, #1d4ed8, #1d4ed8);
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner-sm {
  animation: spin 0.75s linear infinite;
}
</style>