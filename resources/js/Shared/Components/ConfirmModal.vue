<template>
  <teleport to="body">
    <Transition name="modal">
      <div
        v-if="isVisible"
        class="kt-modal"
        data-kt-modal="true"
        :id="modalId"
        ref="modalEl"
      >
        <div class="kt-modal-content max-w-100 top-[15%]">
          <div class="kt-modal-header py-4 px-5">
            <h3 class="kt-modal-title">{{ currentTitle }}</h3>
            <button
              class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0 text-white"
              data-kt-modal-dismiss="true"
              @click="close(false)"
            >
              <i class="ki-filled ki-cross text-white"></i>
            </button>
          </div>
          <div class="kt-modal-body p-5">
            <p>{{ currentMessage }}</p>
          </div>
          <div class="kt-modal-footer flex justify-end gap-2 p-5">
            <button
              class="kt-btn kt-btn-light !bg-gray-200 !text-gray-700 hover:!bg-gray-300"
              data-kt-modal-dismiss="true"
              @click="close(false)"
            >
              Cancelar
            </button>
            <button
              class="kt-btn kt-btn-destructive bg-red-500 text-white hover:bg-red-600"
              @click="close(true)"
            >
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
  modalId: { type: String, default: 'confirm_modal' }
})

const emit = defineEmits(['confirm', 'cancel'])

const modalEl = ref(null)
const currentTitle = ref('Confirmar')
const currentMessage = ref('Tem certeza?')
const isVisible = ref(false)
let resolvePromise = null

const open = (options = {}) => {
  if (options.title) currentTitle.value = options.title
  if (options.message) currentMessage.value = options.message
  return new Promise((resolve) => {
    resolvePromise = resolve
    isVisible.value = true
    nextTick(() => {
      showModal()
    })
  })
}

const showModal = () => {
  if (!window.KTModal) {
    modalEl.value.style.display = 'block'
    return
  }

  let modal = window.KTModal.getInstance(modalEl.value)
  if (!modal) {
    window.KTModal.createInstances()
    modal = window.KTModal.getInstance(modalEl.value)
  }

  modal ? modal.show() : modalEl.value.style.display = 'block'
}

const close = (confirmed) => {
  if (resolvePromise) {
    resolvePromise(confirmed)
    resolvePromise = null
  }
  emit(confirmed ? 'confirm' : 'cancel')
  hideModal()
  isVisible.value = false
}

const hideModal = () => {
  if (!window.KTModal) {
    modalEl.value.style.display = 'none'
    return
  }

  const modal = window.KTModal.getInstance(modalEl.value)
  modal ? modal.hide() : modalEl.value.style.display = 'none'
}

onMounted(() => {
  if (window.KTModal) {
    window.KTModal.createInstances()
  }
})

// Expor open para uso externo
defineExpose({ open })
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
  transform: scale(0.9);
}

.modal-enter-to,
.modal-leave-from {
  opacity: 1;
}

.modal-enter-to > div,
.modal-leave-from > div {
  transform: scale(1);
}
</style>