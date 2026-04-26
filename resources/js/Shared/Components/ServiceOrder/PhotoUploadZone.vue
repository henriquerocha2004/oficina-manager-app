<template>
  <div class="space-y-4">
    <!-- Photo Upload Zone -->
    <div
      v-if="canUploadMore"
      class="border-2 border-dashed rounded-lg transition-colors"
      :class="isDragging
        ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/10'
        : 'border-gray-300 dark:border-gray-600 hover:border-orange-400 dark:hover:border-orange-500'"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
    >
      <label
        class="flex flex-col items-center justify-center py-12 cursor-pointer"
        :class="{ 'pointer-events-none opacity-50': uploading || disabled }"
      >
        <input
          ref="fileInput"
          type="file"
          class="hidden"
          accept="image/jpeg,image/jpg,image/png,image/webp"
          :disabled="uploading || disabled"
          @change="handleFileSelect"
        />

        <i
          v-if="!uploading"
          class="ki-filled ki-file-up text-3xl text-gray-300 dark:text-gray-500"
        ></i>
        <i
          v-else
          class="ki-filled ki-arrows-circle animate-spin text-3xl text-orange-500"
        ></i>

        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
          <span v-if="!uploading">
            Arraste fotos ou <span class="text-orange-600 font-medium">clique para enviar</span>
          </span>
          <span v-else>Enviando foto...</span>
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
          Máx. 30MB • JPEG, PNG, WEBP • {{ photos.length }}/{{ maxPhotos }} fotos
        </p>
      </label>
    </div>

    <!-- Limit Reached Message -->
    <div
      v-else
      class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4"
    >
      <div class="flex items-center gap-3">
        <i class="ki-filled ki-information text-amber-600 dark:text-amber-400"></i>
        <p class="text-sm text-amber-700 dark:text-amber-300">
          Limite de {{ maxPhotos }} fotos atingido. Remova uma foto para adicionar outra.
        </p>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="error"
      class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4"
    >
      <div class="flex items-start gap-3">
        <i class="ki-filled ki-information text-red-600 dark:text-red-400"></i>
        <div class="flex-1">
          <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
        </div>
        <button @click="error = null">
          <i class="ki-filled ki-cross text-xs text-red-400"></i>
        </button>
      </div>
    </div>

    <!-- Photo Grid -->
    <div v-if="photos.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <div
        v-for="photo in photos"
        :key="photo.id"
        class="group relative aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border dark:border-gray-600"
      >
        <img
          :src="photo.url"
          :alt="photo.original_filename"
          class="w-full h-full object-cover cursor-pointer"
          @click="openLightbox(photo)"
        />

        <!-- Overlay on hover -->
        <div
          class="absolute inset-0 bg-transparent group-hover:bg-black/50 transition-all duration-200 flex items-center justify-center gap-2"
        >
          <button
            class="opacity-0 group-hover:opacity-100 kt-btn kt-btn-sm kt-btn-primary"
            @click="openLightbox(photo)"
          >
            <i class="ki-filled ki-eye text-xs"></i>
          </button>

          <button
            v-if="!disabled"
            class="opacity-0 group-hover:opacity-100 kt-btn kt-btn-sm kt-btn-danger"
            :disabled="deleting === photo.id"
            @click="handleDeletePhoto(photo.id)"
          >
            <i
              v-if="deleting === photo.id"
              class="ki-filled ki-arrows-circle animate-spin text-xs"
            ></i>
            <i v-else class="ki-filled ki-trash text-xs"></i>
          </button>
        </div>

        <!-- File info -->
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-2">
          <p class="text-xs text-white truncate">{{ photo.original_filename }}</p>
          <p class="text-xs text-gray-300">{{ formatFileSize(photo.file_size) }}</p>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!canUploadMore"
      class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm"
    >
      Nenhuma foto adicionada ainda.
    </div>

    <!-- Lightbox Modal -->
    <Teleport to="body">
      <div
        v-if="lightbox.isOpen"
        class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
        @click="closeLightbox"
        @keyup.escape="closeLightbox"
      >
        <div class="relative max-w-screen-lg max-h-screen-90 w-full h-full p-4">
          <!-- Close Button -->
          <button
            class="absolute top-4 right-4 z-10 kt-btn kt-btn-sm kt-btn-light"
            @click="closeLightbox"
          >
            <i class="ki-filled ki-cross text-lg"></i>
          </button>

          <!-- Navigation -->
          <button
            v-if="photos.length > 1"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-10 kt-btn kt-btn-sm kt-btn-light"
            @click.stop="previousPhoto"
          >
            <i class="ki-filled ki-left text-lg"></i>
          </button>

          <button
            v-if="photos.length > 1"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-10 kt-btn kt-btn-sm kt-btn-light"
            @click.stop="nextPhoto"
          >
            <i class="ki-filled ki-right text-lg"></i>
          </button>

          <!-- Image Container -->
          <div class="w-full h-full flex items-center justify-center" @click.stop>
            <img
              v-if="lightbox.currentPhoto"
              :src="lightbox.currentPhoto.url"
              :alt="lightbox.currentPhoto.original_filename"
              class="max-w-full max-h-full object-contain"
            />
          </div>

          <!-- Image Info & Actions -->
          <div class="absolute bottom-4 left-4 right-4">
            <div class="bg-black bg-opacity-60 rounded-lg p-4 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium">{{ lightbox.currentPhoto?.original_filename }}</p>
                  <p class="text-sm text-gray-300">
                    {{ formatFileSize(lightbox.currentPhoto?.file_size || 0) }} •
                    {{ lightbox.currentPhoto?.width }} × {{ lightbox.currentPhoto?.height }}
                  </p>
                </div>
                <div class="flex gap-2">
                  <a
                    :href="lightbox.currentPhoto?.url"
                    :download="lightbox.currentPhoto?.original_filename"
                    class="kt-btn kt-btn-sm kt-btn-primary"
                    @click.stop
                  >
                    <i class="ki-filled ki-download text-xs"></i>
                    Download
                  </a>
                </div>
              </div>

              <!-- Photo Counter -->
              <div v-if="photos.length > 1" class="mt-2 text-center text-sm text-gray-400">
                {{ getCurrentPhotoIndex() + 1 }} / {{ photos.length }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { uploadServiceOrderPhoto, deleteServiceOrderPhoto } from '@/services/serviceOrderService.js';

const props = defineProps({
  serviceOrderId: {
    type: String,
    required: true,
  },
  serviceOrderNumber: {
    type: String,
    required: true,
  },
  photos: {
    type: Array,
    default: () => [],
  },
  maxPhotos: {
    type: Number,
    default: 5,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['uploaded', 'deleted', 'error']);

const fileInput = ref(null);
const uploading = ref(false);
const deleting = ref(null);
const error = ref(null);
const isDragging = ref(false);

// Lightbox state
const lightbox = ref({
  isOpen: false,
  currentPhoto: null,
});

const canUploadMore = computed(() => props.photos.length < props.maxPhotos);

const VALID_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const MAX_FILE_SIZE = 30 * 1024 * 1024; // 30MB

function validateFile(file) {
  if (!VALID_TYPES.includes(file.type)) {
    return 'Formato inválido. Use JPEG, PNG ou WEBP.';
  }

  if (file.size > MAX_FILE_SIZE) {
    return 'Arquivo muito grande. Máximo 30MB.';
  }

  return null;
}

async function handleFileSelect(event) {
  const file = event.target.files[0];
  if (!file) return;

  await uploadPhoto(file);

  // Reset input
  if (fileInput.value) {
    fileInput.value.value = '';
  }
}

function handleDrop(event) {
  isDragging.value = false;

  const file = event.dataTransfer.files[0];
  if (!file) return;

  uploadPhoto(file);
}

async function uploadPhoto(file) {
  error.value = null;

  const validationError = validateFile(file);
  if (validationError) {
    error.value = validationError;
    emit('error', validationError);
    return;
  }

  uploading.value = true;
  const result = await uploadServiceOrderPhoto(props.serviceOrderId, file, {
    serviceOrderNumber: props.serviceOrderNumber,
  });
  uploading.value = false;

  if (result.success) {
    emit('uploaded', result.data);
  } else {
    error.value = result.error || 'Erro ao enviar foto.';
    emit('error', result.error);
  }
}

async function handleDeletePhoto(photoId) {
  deleting.value = photoId;
  const result = await deleteServiceOrderPhoto(props.serviceOrderId, photoId, {
    serviceOrderNumber: props.serviceOrderNumber,
  });
  deleting.value = null;

  if (result.success) {
    emit('deleted', photoId);
  } else {
    error.value = result.error || 'Erro ao excluir foto.';
    emit('error', result.error);
  }
}

function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes <1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// Lightbox functions
function openLightbox(photo) {
  lightbox.value = {
    isOpen: true,
    currentPhoto: photo,
  };
  document.body.style.overflow = 'hidden'; // Prevent background scroll
}

function closeLightbox() {
  lightbox.value = {
    isOpen: false,
    currentPhoto: null,
  };
  document.body.style.overflow = ''; // Restore scrolling
}

function getCurrentPhotoIndex() {
  if (!lightbox.value.currentPhoto) return 0;
  return props.photos.findIndex(p => p.id === lightbox.value.currentPhoto.id);
}

function previousPhoto() {
  const currentIndex = getCurrentPhotoIndex();
  const prevIndex = currentIndex > 0 ? currentIndex - 1 : props.photos.length - 1;
  lightbox.value.currentPhoto = props.photos[prevIndex];
}

function nextPhoto() {
  const currentIndex = getCurrentPhotoIndex();
  const nextIndex = currentIndex < props.photos.length - 1 ? currentIndex + 1 : 0;
  lightbox.value.currentPhoto = props.photos[nextIndex];
}

// Keyboard navigation
function handleKeyDown(event) {
  if (!lightbox.value.isOpen) return;

  if (event.key === 'ArrowLeft') {
    event.preventDefault();
    previousPhoto();
  } else if (event.key === 'ArrowRight') {
    event.preventDefault();
    nextPhoto();
  } else if (event.key === 'Escape') {
    event.preventDefault();
    closeLightbox();
  }
}

// Add keyboard listeners when component mounts
onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown);
  document.body.style.overflow = ''; // Ensure scroll is restored
});
</script>
