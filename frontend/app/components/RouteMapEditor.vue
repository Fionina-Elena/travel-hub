<template>
  <div class="container">
    <div :id="mapContainerId" class="map"></div>
    <p class="hint">Кликните на карту чтобы добавить точку</p>
    <div v-if="points.length" class="points-list">
      <div v-for="(point, idx) in points" :key="idx" class="point">
        <span class="point-num">{{ idx + 1 }}.</span>
        <span class="point-name">{{ point.name || 'Без названия' }}</span>
        <button @click="removePoint(idx)" type="button" class="point-remove">×</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps<{
  modelValue: any[]
}>()

const emit = defineEmits(['update:modelValue'])

const mapContainerId = 'map-' + Math.random().toString(36).substr(2, 9)

const points = ref<any[]>([...props.modelValue])

let map: any = null
let ymapsLib: any = null
let isMapReady = false

const getCenterFromPoints = () => {
  for (const point of points.value) {
    if (point.coords && Array.isArray(point.coords)) {
      return point.coords
    }
  }
  return [55.75399399999374, 37.62209300000001]
}

const initMap = () => {
  const mapEl = document.getElementById(mapContainerId)
  if (!mapEl) {
    setTimeout(initMap, 200)
    return
  }
  
  ymapsLib = (window as any).ymaps
  const center = getCenterFromPoints()
  
  map = new ymapsLib.Map(mapContainerId, {
    center: center,
    zoom: 10,
    controls: ['zoomControl', 'searchControl', 'fullscreenControl']
  })

  map.events.add('click', handleMapClick)
  isMapReady = true
  
  nextTick(() => renderPlacemarks())
}

const renderPlacemarks = () => {
  if (!map || !ymapsLib || !isMapReady) return
  
  map.geoObjects.removeAll()

  points.value.forEach((point, index) => {
    let coords = point.coords
    
    if (!coords) return
    
    if (typeof coords === 'string') {
      const res = ymapsLib.geocode(coords)
      const geoObject = res.geoObjects.get(0)
      if (geoObject) {
        coords = geoObject.geometry.getCoordinates()
      }
    }
    
    if (Array.isArray(coords)) {
      const placemark = new ymapsLib.Placemark(coords, {
        iconContent: String(index + 1),
        preset: index === 0 ? 'islands#greenCircleIcon' : 'islands#blueCircleIcon'
      })
      
      placemark.events.add('click', (e: any) => {
        e.stopPropagation()
        removePoint(index)
      })
      
      map.geoObjects.add(placemark)
    }
  })
}

const handleMapClick = async (e: any) => {
  if (!ymapsLib || !map) return

  const coords = e.get('coords')
  
  const res = await ymapsLib.geocode(coords, { results: 1 })
  const geoObject = res.geoObjects.get(0)
  const name = geoObject ? geoObject.getAddressLine() : coords.join(', ')

  const newPoints = [...points.value, { name, coords }]
  points.value = newPoints
  emit('update:modelValue', newPoints)
}

const removePoint = (index: number) => {
  const newPoints = points.value.filter((_, i) => i !== index)
  points.value = newPoints
  emit('update:modelValue', newPoints)
}

watch(() => props.modelValue, (newVal) => {
  points.value = [...newVal]
  if (isMapReady) {
    nextTick(() => renderPlacemarks())
  }
}, { deep: true })

onMounted(() => {
  const checkYmaps = setInterval(() => {
    if ((window as any).ymaps) {
      clearInterval(checkYmaps)
      ymapsLib = (window as any).ymaps
      ymapsLib.ready(initMap)
    }
  }, 100)
})
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.map {
  width: 100%;
  height: 20rem;
  border-radius: 0.5rem;
  background: #f3f4f6;
  cursor: crosshair;
}

.hint {
  font-size: 0.875rem;
  color: #6b7280;
}

.points-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.point {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #f3f4f6;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
}

.point-num {
  color: #4b5563;
}

.point-name {
  font-weight: 500;
}

.point-remove {
  color: #ef4444;
  background: none;
  border: none;
  cursor: pointer;
  margin-left: 0.25rem;
}

.point-remove:hover {
  color: #dc2626;
}
</style>