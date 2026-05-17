<template>
  <div class="page">
    <div class="header">
      <h1 class="title">Категории</h1>
      <button @click="openForm()" class="btn">+ Добавить</button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>
    
    <div v-else class="grid">
      <div v-for="cat in categories" :key="cat.id" class="category-card">
        <div class="category-info">
          <h3 class="category-name">{{ cat.name }}</h3>
          <p class="category-desc">{{ cat.description || 'Без описания' }}</p>
        </div>
        <div class="category-actions">
          <button @click="openForm(cat)" class="btn-icon btn-edit">✏️</button>
          <button @click="deleteCat(cat.id)" class="btn-icon btn-delete">🗑️</button>
        </div>
      </div>
      <div v-if="categories.length === 0" class="empty">
        <div class="empty-icon">📁</div>
        <p class="empty-text">Нет категорий</p>
      </div>
    </div>

    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal">
        <h2 class="modal-title">{{ editingCat ? '✏️ Изменить категорию' : '➕ Новая категория' }}</h2>
        <div class="form">
          <div class="form-group">
            <label class="label">Название</label>
            <input v-model="name" type="text" placeholder="Пляжный отдых" class="input" />
          </div>
          <div class="form-group">
            <label class="label">Описание</label>
            <textarea v-model="description" rows="2" placeholder="Краткое описание..." class="textarea"></textarea>
          </div>
        </div>
        <div class="modal-actions">
          <button @click="showForm = false" class="btn-cancel">Отмена</button>
          <button @click="save" class="btn-save">Сохранить</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { categories as categoriesApi } from '../../api'

const categories = ref<any[]>([])
const loading = ref(true)
const showForm = ref(false)
const editingCat = ref<any>(null)
const name = ref('')
const description = ref('')

const fetchCategories = async () => {
  loading.value = true
  const response = await categoriesApi.getAll()
  categories.value = response.data
  loading.value = false
}

const openForm = (cat?: any) => {
  editingCat.value = cat
  name.value = cat?.name || ''
  description.value = cat?.description || ''
  showForm.value = true
}

const save = async () => {
  if (editingCat.value) {
    await categoriesApi.update(editingCat.value.id, { name: name.value, description: description.value })
  } else {
    await categoriesApi.create({ name: name.value, description: description.value })
  }
  showForm.value = false
  fetchCategories()
}

const deleteCat = async (id: number) => {
  if (!confirm('Удалить категорию?')) return
  await categoriesApi.delete(id)
  fetchCategories()
}

onMounted(fetchCategories)
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

.grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 1rem;
}

@media (min-width: 768px) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.category-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  padding: 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.category-name {
  font-weight: 700;
  font-size: 1.125rem;
  margin-bottom: 0.25rem;
}

.category-desc {
  color: #6b7280;
  font-size: 0.875rem;
}

.category-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  padding: 0.5rem;
  border-radius: 0.5rem;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
  background: transparent;
}

.btn-edit {
  color: #2563eb;
}

.btn-edit:hover {
  background: #dbeafe;
}

.btn-delete {
  color: #ef4444;
}

.btn-delete:hover {
  background: #fef2f2;
}

.empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 2.5rem 0;
  background: white;
  border-radius: 1rem;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-text {
  color: #6b7280;
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
  max-width: 28rem;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
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

.input,
.textarea {
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.75rem;
  font-size: 1rem;
  width: 100%;
}

.input:focus,
.textarea:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.5);
}

.textarea {
  resize: none;
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
  border-radius: 0.75rem;
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
  border-radius: 0.75rem;
  cursor: pointer;
}

.btn-save:hover {
  background: #1d4ed8;
}
</style>