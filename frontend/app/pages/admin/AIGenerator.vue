<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">🤖 AI Генерация туров</h2>
      <span :class="status.ollama ? 'status-on' : 'status-off'" class="status">
        {{ status.ollama ? 'Ollama подключен' : 'Ollama недоступен' }}
      </span>
    </div>

    <div v-if="!status.ollama" class="alert">
      <p class="alert-text">💡 Для генерации туров установите и запустите Ollama:</p>
      <div class="code">
        ollama pull nomic-embed-text<br>
        ollama pull llama3.2:3b<br>
        ollama serve
      </div>
    </div>

    <div class="form">
      <div class="form-group">
        <label class="label">Категория</label>
        <select v-model="form.category_id" class="input">
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>

      <div class="form-group">
        <label class="label">Описание тура</label>
        <textarea v-model="form.prompt" rows="3" placeholder="Опишите какой тур вы хотите создать, например: 'Недельный тур на море с детьми, акцент на развлечениях'" class="textarea"></textarea>
      </div>

      <button @click="generate" :disabled="!form.prompt || !form.category_id || generating" class="btn-generate">
        <span v-if="generating" class="spinner">⏳</span>
        <span>{{ generating ? 'Генерация...' : '✨ Сгенерировать тур' }}</span>
      </button>
    </div>

    <div v-if="generated" class="result">
      <h3 class="result-title">✨ Сгенерированный тур:</h3>
      <div class="result-content">
        <div class="result-item">
          <span class="result-label">Название:</span>
          <span class="result-value">{{ generated.title }}</span>
        </div>
        <div class="result-item">
          <span class="result-label">Описание:</span>
          <p class="result-desc">{{ generated.description }}</p>
        </div>
        <div class="result-item">
          <span class="result-label">Дней:</span>
          <span class="result-value">{{ generated.duration_days }}</span>
        </div>
        <div v-if="generated.highlights" class="result-item">
          <span class="result-label">Особенности:</span>
          <ul class="highlights-list">
            <li v-for="(h, i) in generated.highlights" :key="i">{{ h }}</li>
          </ul>
        </div>
      </div>
      <button @click="useGenerated" class="btn-use">✅ Использовать для нового тура</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { categories as categoriesApi } from '../../api'

const emit = defineEmits(['tour-generated'])

const categories = ref<any[]>([])
const status = ref({ ollama: false, embedding_model: false, llm_model: false })
const generating = ref(false)
const generated = ref<any>(null)

const form = ref({
  category_id: null as number | null,
  prompt: ''
})

const checkStatus = async () => {
  try {
    const response = await fetch('/api/v1/ai/status')
    status.value = await response.json()
  } catch {}
}

const fetchCategories = async () => {
  const response = await categoriesApi.getAll()
  categories.value = response.data
  if (categories.value.length > 0) {
    form.value.category_id = categories.value[0].id
  }
}

const generate = async () => {
  generating.value = true
  generated.value = null
  
  try {
    const response = await fetch('/api/v1/ai/generate-tour', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    })
    
    if (response.ok) {
      generated.value = await response.json()
    } else {
      const err = await response.json()
      alert(err.error || 'Ошибка генерации')
    }
  } finally {
    generating.value = false
  }
}

const useGenerated = () => {
  emit('tour-generated', {
    ...generated.value,
    category_id: form.value.category_id
  })
  generated.value = null
  form.value.prompt = ''
}

onMounted(() => {
  checkStatus()
  fetchCategories()
})
</script>

<style scoped>
.card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  padding: 1.5rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-on {
  background: #d1fae5;
  color: #047857;
}

.status-off {
  background: #fef2f2;
  color: #b91c1c;
}

.alert {
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.alert-text {
  color: #b45309;
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
}

.code {
  background: #fef3c7;
  padding: 0.75rem;
  border-radius: 0.5rem;
  font-family: monospace;
  font-size: 0.875rem;
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
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.75rem;
  font-size: 1rem;
  background: white;
}

.input:focus,
.textarea:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.5);
}

.textarea {
  resize: none;
}

.btn-generate {
  width: 100%;
  background: linear-gradient(135deg, #7c3aed, #6366f1);
  color: white;
  padding: 0.75rem;
  border-radius: 0.75rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn-generate:hover:not(:disabled) {
  background: linear-gradient(135deg, #6d28d9, #4f46e5);
}

.btn-generate:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner {
  animation: spin 0.75s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.result {
  margin-top: 1.5rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #f3e8ff, #e0e7ff);
  border-radius: 0.75rem;
  border: 1px solid #c4b5fd;
}

.result-title {
  font-weight: 700;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.result-content {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.result-item {
  background: white;
  padding: 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.result-label {
  color: #6b7280;
}

.result-value {
  font-weight: 500;
  margin-left: 0.5rem;
}

.result-desc {
  margin-top: 0.25rem;
}

.highlights-list {
  list-style: disc;
  padding-left: 1.25rem;
  margin-top: 0.25rem;
}

.btn-use {
  width: 100%;
  background: #059669;
  color: white;
  padding: 0.75rem;
  border-radius: 0.75rem;
  font-weight: 500;
  margin-top: 1rem;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-use:hover {
  background: #047857;
}
</style>