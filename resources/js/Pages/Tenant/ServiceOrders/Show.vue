<template>
  <TenantLayout :title="serviceOrder ? `OS-${serviceOrder.order_number}` : 'OS'" :breadcrumbs="breadcrumbs">
    <div v-if="serviceOrder" class="sm:-m-5 lg:-m-10 flex flex-col h-full">

      <!-- Header da OS -->
      <div class="flex items-center gap-2 px-4 py-2 border-b border-gray-200 dark:border-gray-700 shrink-0">
        <button
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors shrink-0"
          @click="goBack"
        >
          <i class="ki-filled ki-arrow-left text-lg"></i>
        </button>

        <div class="flex items-center gap-1.5 text-sm min-w-0 flex-1">
          <span class="font-bold text-gray-900 dark:text-gray-100 shrink-0">OS-{{ serviceOrder?.order_number }}</span>
          <span class="text-gray-300 dark:text-gray-600 hidden lg:inline">•</span>
          <span class="text-gray-600 dark:text-gray-400 truncate hidden lg:inline">{{ serviceOrder.client?.name }}</span>
          <span class="text-gray-300 dark:text-gray-600 hidden lg:inline">•</span>
          <span class="text-gray-600 dark:text-gray-400 truncate hidden lg:inline">{{ vehicleLabel }}</span>
          <span class="hidden lg:inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-600 shrink-0">
            {{ serviceOrder.vehicle?.license_plate }}
          </span>
        </div>

        <div class="ml-auto flex items-center gap-1.5 shrink-0">
          <span class="hidden lg:inline-flex items-center px-2.5 py-1 rounded text-xs font-medium" :class="statusColor">
            {{ statusLabel }}
          </span>

          <!-- Botão Imprimir PDF -->
          <a
            :href="`/service-orders/${serviceOrder.id}/pdf`"
            target="_blank"
            class="kt-btn kt-btn-sm kt-btn-icon"
            title="Imprimir OS (PDF)"
          >
            <i class="ki-filled ki-printer"></i>
          </a>

          <!-- Botão Primário de Transição -->
          <button
            v-if="getPrimaryAction(serviceOrder.status)"
            class="kt-btn kt-btn-primary kt-btn-sm max-w-32.5 truncate"
            :disabled="transitioning"
            :title="getTransitionLabel(serviceOrder.status, getPrimaryAction(serviceOrder.status))"
            @click="handleTransition(getPrimaryAction(serviceOrder.status))"
          >
            <i v-if="transitioning" class="ki-filled ki-arrows-circle animate-spin text-xs mr-1 shrink-0"></i>
            <span class="truncate">{{ getTransitionLabel(serviceOrder.status, getPrimaryAction(serviceOrder.status)) }}</span>
          </button>

          <!-- Botões Secundários -->
          <div v-if="getSecondaryActions(serviceOrder.status).length > 0 || !['completed', 'cancelled'].includes(serviceOrder.status)" class="relative">
            <button class="kt-btn kt-btn-sm kt-btn-icon" @click.stop="showActionsDropdown = !showActionsDropdown">
              <i class="ki-filled ki-dots-vertical"></i>
            </button>
            <div
              v-if="showActionsDropdown"
              class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
              @click.stop
            >
              <!-- Ações secundárias de transição -->
              <button
                v-for="action in getSecondaryActions(serviceOrder.status)"
                :key="action"
                class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 first:rounded-t-lg"
                @click="handleTransition(action); showActionsDropdown = false"
              >
                {{ getTransitionLabel(serviceOrder.status, action) }}
              </button>

              <!-- Cancelar OS (se não for terminal) -->
              <button
                v-if="!['completed', 'cancelled'].includes(serviceOrder.status)"
                class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 border-t border-gray-100 dark:border-gray-600 last:rounded-b-lg"
                @click="handleCancelFromFinanceiro(); showActionsDropdown = false"
              >
                🚫 Cancelar OS
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile only: info summary (substitui sidebar que fica hidden) -->
      <div class="lg:hidden shrink-0 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        <button
          class="w-full flex items-center justify-between px-4 py-2.5 text-xs"
          @click="showMobileInfo = !showMobileInfo"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div class="flex items-center gap-1.5 min-w-0">
              <i class="ki-filled ki-profile-circle text-gray-400 shrink-0"></i>
              <span class="font-medium text-gray-700 dark:text-gray-300 truncate">{{ serviceOrder.client?.name }}</span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              <i class="ki-filled ki-car text-gray-400"></i>
              <span class="text-gray-500 dark:text-gray-400">{{ serviceOrder.vehicle?.license_plate }}</span>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0 ml-2">
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(serviceOrder.total || 0) }}</span>
            <i class="ki-filled text-gray-400 text-[10px]" :class="showMobileInfo ? 'ki-arrow-up' : 'ki-arrow-down'"></i>
          </div>
        </button>
        <div v-if="showMobileInfo" class="px-4 pb-3 space-y-3 border-t border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-2 gap-3 pt-2.5">
            <!-- Cliente -->
            <div>
              <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Cliente</p>
              <p class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ serviceOrder.client?.name }}</p>
              <p v-if="serviceOrder.client?.phone" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                <i class="ki-filled ki-phone text-[10px]"></i>
                {{ serviceOrder.client.phone }}
              </p>
            </div>
            <!-- Veículo -->
            <div>
              <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Veículo</p>
              <p class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ vehicleLabel }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ serviceOrder.vehicle?.license_plate }}<span v-if="serviceOrder.vehicle?.year"> • {{ serviceOrder.vehicle.year }}</span>
              </p>
              <p v-if="serviceOrder.vehicle?.mileage" class="text-xs text-gray-500 dark:text-gray-400">
                {{ formatMileage(serviceOrder.vehicle.mileage) }}
              </p>
            </div>
          </div>
          <!-- Resumo financeiro -->
          <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
            <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Resumo Financeiro</p>
            <div class="space-y-1 text-xs">
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Serviços</span>
                <span class="text-gray-700 dark:text-gray-300">{{ formatCurrency(serviceOrder.total_services || 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Peças</span>
                <span class="text-gray-700 dark:text-gray-300">{{ formatCurrency(serviceOrder.total_parts || 0) }}</span>
              </div>
              <div v-if="serviceOrder.discount > 0" class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Desconto</span>
                <span class="text-green-500">- {{ formatCurrency(serviceOrder.discount) }}</span>
              </div>
              <div class="flex justify-between font-semibold pt-1 border-t border-gray-100 dark:border-gray-800">
                <span class="text-gray-900 dark:text-gray-100">Total</span>
                <span class="text-gray-900 dark:text-gray-100">{{ formatCurrency(serviceOrder.total || 0) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Corpo: sidebar + conteúdo -->
      <div class="flex flex-1 min-h-0 overflow-hidden">

        <!-- Sidebar esquerda -->
        <aside class="hidden lg:block w-52 shrink-0 border-r border-gray-200 dark:border-gray-700 overflow-y-auto px-4 py-5 space-y-5">

          <!-- Cliente -->
          <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Cliente</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ serviceOrder.client?.name }}</p>
            <p v-if="serviceOrder.client?.phone" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
              <i class="ki-filled ki-phone text-[10px]"></i>
              {{ serviceOrder.client.phone }}
            </p>
          </div>

          <!-- Veículo -->
          <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Veículo</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1">
              <i class="ki-filled ki-car text-gray-400 text-xs"></i>
              {{ vehicleLabel }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              {{ serviceOrder.vehicle?.license_plate }}<span v-if="serviceOrder.vehicle?.year"> • {{ serviceOrder.vehicle.year }}</span>
            </p>
            <p v-if="serviceOrder.vehicle?.mileage" class="text-xs text-gray-500 dark:text-gray-400">
              {{ formatMileage(serviceOrder.vehicle.mileage) }}
            </p>
            <button
              class="mt-1.5 text-xs text-orange-600 hover:text-orange-700 font-medium"
              @click="router.visit(`/vehicles/${serviceOrder.vehicle?.id}/history`)"
            >
              Ver histórico do veículo →
            </button>
          </div>

          <hr class="border-gray-200 dark:border-gray-700" />

          <!-- Resumo financeiro -->
          <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Resumo Financeiro</p>
            <div class="space-y-1.5 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Serviços</span>
                <span class="text-gray-700 dark:text-gray-300">{{ formatCurrency(serviceOrder.total_services || 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Peças</span>
                <span class="text-gray-700 dark:text-gray-300">{{ formatCurrency(serviceOrder.total_parts || 0) }}</span>
              </div>
              <div v-if="serviceOrder.discount > 0" class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Desconto</span>
                <span class="text-green-500">- {{ formatCurrency(serviceOrder.discount) }}</span>
              </div>
              <div class="flex justify-between font-semibold pt-1 border-t border-gray-100 dark:border-gray-800">
                <span class="text-gray-900 dark:text-gray-100">Total</span>
                <span class="text-gray-900 dark:text-gray-100">{{ formatCurrency(serviceOrder.total || 0) }}</span>
              </div>
            </div>
          </div>

        </aside>

        <!-- Conteúdo principal -->
        <main class="flex-1 flex flex-col min-h-0">

          <!-- Tabs -->
          <div class="border-b border-gray-200 dark:border-gray-700 shrink-0">
            <nav class="flex">
              <button
                v-for="tab in tabs"
                :key="tab.key"
                :title="tab.label"
                class="flex-1 flex items-center justify-center gap-1.5 px-2 py-3 text-sm font-medium border-b-2 transition-colors"
                :class="activeTab === tab.key
                  ? 'border-orange-500 text-orange-600 dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                @click="activeTab = tab.key"
              >
                <i :class="tab.icon" class="text-sm shrink-0"></i>
                <span class="hidden sm:inline truncate">{{ tab.label }}</span>
              </button>
            </nav>
          </div>

          <!-- Conteúdo das tabs -->
          <div class="p-4 sm:p-6 flex-1 overflow-y-auto" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">

            <!-- Diagnóstico -->
            <div v-if="activeTab === 'diagnostico'" class="w-full max-w-2xl space-y-6">
              <!-- Banner: OS Cancelada -->
              <div
                v-if="serviceOrder.status === 'cancelled'"
                class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4"
              >
                <div class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                    <i class="ki-filled ki-information text-red-600 dark:text-red-400"></i>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-red-900 dark:text-red-100">
                      Ordem de Serviço Cancelada
                    </p>
                    <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                      Esta OS está cancelada e não pode ser editada.
                    </p>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Problema Relatado</h3>
                <div class="rounded-lg bg-gray-100 dark:bg-gray-800 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 min-h-[48px]">
                  {{ serviceOrder?.reported_problem || '—' }}
                </div>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Diagnóstico do Técnico</h3>
                <textarea
                  v-model="diagnosisInput"
                  class="kt-input w-full"
                  rows="5"
                  style="min-height: 120px; resize: vertical;"
                  placeholder="Descreva os achados do diagnóstico..."
                  :disabled="serviceOrder.status === 'cancelled'"
                  @blur="handleDiagnosisBlur"
                ></textarea>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Fotos</h3>
                <PhotoUploadZone
                  :service-order-id="serviceOrder.id"
                  :service-order-number="serviceOrder.order_number"
                  :photos="serviceOrder.photos || []"
                  :disabled="serviceOrder.status === 'cancelled'"
                  @uploaded="handlePhotoUploaded"
                  @deleted="handlePhotoDeleted"
                  @error="handlePhotoError"
                />
              </div>
            </div>

            <!-- Itens -->
            <div v-else-if="activeTab === 'itens'" class="w-full max-w-3xl">

              <!-- Banner: OS Cancelada -->
              <div
                v-if="serviceOrder.status === 'cancelled'"
                class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 mb-4"
              >
                <div class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                    <i class="ki-filled ki-information text-red-600 dark:text-red-400"></i>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-red-900 dark:text-red-100">
                      Ordem de Serviço Cancelada
                    </p>
                    <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                      Esta OS está cancelada e não pode ser editada.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Cabeçalho da aba -->
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Itens da Ordem de Serviço</h3>
                <button
                  v-if="!newItem && serviceOrder.status !== 'cancelled'"
                  type="button"
                  class="kt-btn kt-btn-sm kt-btn-primary"
                  @click="startAddItem"
                >
                  <i class="ki-filled ki-plus text-xs"></i>
                  Adicionar Item
                </button>
              </div>

              <!-- Erro -->
              <div v-if="itemError" class="mb-3 px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                <i class="ki-filled ki-information-2"></i>
                {{ itemError }}
                <button class="ml-auto" @click="itemError = null"><i class="ki-filled ki-cross text-xs"></i></button>
              </div>

              <!-- Lista de itens existentes -->
              <div v-if="items.length > 0" class="mb-4 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                      <th class="text-left px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 w-20">Tipo</th>
                      <th class="text-left px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400">Descrição</th>
                      <th class="hidden sm:table-cell text-right px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 w-14">Qtd</th>
                      <th class="hidden sm:table-cell text-right px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 w-28">Preço Unit.</th>
                      <th class="text-right px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 w-24">Subtotal</th>
                      <th class="w-10"></th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                      <td class="px-3 py-2.5">
                        <span
                          class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                          :class="item.type === 'service'
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                            : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'"
                        >
                          {{ item.type === 'service' ? 'Serviço' : 'Peça' }}
                        </span>
                      </td>
                      <td class="px-3 py-2.5">
                        <span class="text-gray-900 dark:text-gray-100">{{ item.description }}</span>
                        <!-- Qtd × preço visível apenas no mobile -->
                        <span class="sm:hidden block text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                          {{ item.quantity }}× {{ formatCurrency(item.unit_price) }}
                        </span>
                      </td>
                      <td class="hidden sm:table-cell px-3 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ item.quantity }}</td>
                      <td class="hidden sm:table-cell px-3 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ formatCurrency(item.unit_price) }}</td>
                      <td class="px-3 py-2.5 text-right font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(item.subtotal) }}</td>
                      <td class="px-3 py-2.5 text-center">
                        <button
                          type="button"
                          class="kt-btn kt-btn-sm kt-btn-icon kt-btn-danger"
                          :disabled="removingItemId === item.id || serviceOrder.status === 'cancelled'"
                          @click="handleRemoveItem(item.id)"
                        >
                          <i v-if="removingItemId === item.id" class="ki-filled ki-arrows-circle animate-spin text-xs"></i>
                          <i v-else class="ki-filled ki-trash text-xs"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <tr>
                      <td colspan="2" class="sm:hidden px-3 py-2 text-sm font-semibold text-right text-gray-700 dark:text-gray-300">Total</td>
                      <td colspan="4" class="hidden sm:table-cell px-3 py-2 text-sm font-semibold text-right text-gray-700 dark:text-gray-300">Total</td>
                      <td class="px-3 py-2 text-right font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(totalItems) }}</td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <!-- Estado vazio -->
              <div v-else-if="!newItem" class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm">
                Nenhum item adicionado. Clique em "Adicionar Item" para começar.
              </div>

              <!-- Formulário de novo item -->
              <div v-if="newItem" class="p-3 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg space-y-3 bg-gray-50 dark:bg-gray-800/50">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Novo Item</p>

                <!-- Mobile: empilhado / Desktop: linha única -->
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                  <!-- Tipo (dropdown customizado) -->
                  <div class="relative w-full sm:w-28 shrink-0" @click.stop>
                    <button
                      type="button"
                      class="kt-input w-full flex items-center justify-between gap-2 cursor-pointer select-none"
                      @click="showTypeDropdown = !showTypeDropdown"
                    >
                      <span>{{ itemTypeOptions.find(o => o.value === newItem.type)?.label }}</span>
                      <i class="ki-outline ki-down text-xs text-gray-400 shrink-0 transition-transform" :class="{ 'rotate-180': showTypeDropdown }"></i>
                    </button>
                    <div
                      v-if="showTypeDropdown"
                      class="absolute left-0 top-full mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 overflow-hidden"
                    >
                      <button
                        v-for="opt in itemTypeOptions"
                        :key="opt.value"
                        type="button"
                        class="w-full text-left px-3 py-2 text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                        :class="newItem.type === opt.value
                          ? 'text-orange-600 dark:text-orange-400 font-medium bg-orange-50 dark:bg-orange-900/20'
                          : 'text-gray-700 dark:text-gray-300'"
                        @click="selectItemType(opt.value)"
                      >
                        {{ opt.label }}
                      </button>
                    </div>
                  </div>

                  <!-- Descrição com dropdown de busca -->
                  <div class="relative w-full sm:flex-1">
                    <input
                      ref="newItemDescInput"
                      v-model="newItem.description"
                      type="text"
                      class="kt-input w-full"
                      :placeholder="newItem.type === 'service' ? 'Buscar serviço ou digite...' : 'Nome da peça'"
                      @input="onNewItemSearch"
                      @focus="onNewItemFocus"
                      @blur="onNewItemBlur"
                    />
                    <teleport to="body">
                      <div
                        v-if="showServiceDropdown && filteredServices.length > 0"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                        :style="serviceDropdownStyle"
                      >
                        <div
                          v-for="service in filteredServices"
                          :key="service.id"
                          class="px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0"
                          @mousedown.prevent="selectService(service)"
                        >
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ service.name }}</div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatCurrency(service.base_price) }}</div>
                        </div>
                      </div>
                    </teleport>
                  </div>

                  <!-- Quantidade + Preço -->
                  <div class="grid grid-cols-2 gap-2 sm:flex sm:gap-2 sm:w-auto">
                    <input
                      v-model.number="newItem.quantity"
                      type="number"
                      class="kt-input w-full sm:w-20"
                      placeholder="Qtd"
                      min="1"
                    />
                    <input
                      v-model.number="newItem.unit_price"
                      type="number"
                      class="kt-input w-full sm:w-28"
                      placeholder="Preço (R$)"
                      step="0.01"
                      min="0"
                    />
                  </div>
                </div>

                <div class="flex gap-2 justify-end pt-1">
                  <button type="button" class="kt-btn kt-btn-sm kt-btn-light" @click="cancelAddItem">Cancelar</button>
                  <button
                    type="button"
                    class="kt-btn kt-btn-sm kt-btn-primary"
                    :disabled="!canSaveNewItem || savingItem"
                    @click="confirmAddItem"
                  >
                    <span v-if="savingItem">Salvando...</span>
                    <span v-else>Salvar Item</span>
                  </button>
                </div>
              </div>

            </div>

            <!-- Linha do Tempo -->
            <div v-else-if="activeTab === 'linha-do-tempo'" class="w-full max-w-2xl">
              <ServiceOrderTimeline :events="serviceOrder.events || []" />
            </div>

            <!-- Financeiro -->
            <div v-else-if="activeTab === 'financeiro'" class="w-full max-w-2xl">
              <ServiceOrderFinanceiro :service-order="serviceOrder" @cancel="handleCancelFromFinanceiro" />
            </div>

          </div>
        </main>
      </div>
    </div>

    <div v-else class="flex items-center justify-center h-64">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
    </div>

    <!-- Modal de Cancelamento -->
    <teleport to="body">
      <div v-if="cancelModalOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
              <i class="ki-filled ki-information text-red-600 dark:text-red-400"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cancelar Ordem de Serviço</h3>
            </div>
          </div>
          <div class="p-6 space-y-4">
            <!-- Aviso de estorno se houver pagamentos -->
            <div v-if="totalPaid > 0" class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg space-y-3">
              <div class="flex items-start gap-2">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-500 flex items-center justify-center mt-0.5">
                  <i class="ki-filled ki-information text-white text-xs"></i>
                </div>
                <div class="flex-1">
                  <p class="text-sm text-amber-800 dark:text-amber-200 font-medium">
                    Esta ação irá cancelar a OS e estornar automaticamente todo o valor pago ao cliente ({{ formatCurrency(totalPaid) }}). Esta ação não pode ser desfeita.
                  </p>
                </div>
              </div>
              <div class="flex items-center justify-between pt-2 mt-3 border-t border-amber-200 dark:border-amber-700">
                <span class="text-sm font-medium text-amber-700 dark:text-amber-300">Total a estornar:</span>
                <span class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ formatCurrency(totalPaid) }}</span>
              </div>
            </div>
            <p v-else class="text-sm text-gray-600 dark:text-gray-400">
              Esta ação não pode ser desfeita. Informe o motivo do cancelamento.
            </p>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Motivo do cancelamento
              </label>
              <textarea
                v-model="cancelReason"
                class="kt-input w-full"
                rows="10"
                style="min-height: 200px; resize: vertical;"
                placeholder="Ex: Cliente desistiu do serviço, problema não identificado..."
              ></textarea>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex gap-3 justify-end">
            <button @click="cancelModalOpen = false; cancelReason = ''" class="kt-btn">Voltar</button>
            <button
              @click="handleCancel"
              :disabled="!cancelReason.trim() || cancelReason.trim().length < 3"
              class="kt-btn kt-btn-danger"
            >
              <i class="ki-filled ki-cross-circle text-xs mr-1"></i>
              Confirmar Cancelamento
            </button>
          </div>
        </div>
      </div>
    </teleport>
  </TenantLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import ServiceOrderTimeline from '@/Shared/Components/ServiceOrder/ServiceOrderTimeline.vue';
import ServiceOrderFinanceiro from '@/Shared/Components/ServiceOrder/ServiceOrderFinanceiro.vue';
import PhotoUploadZone from '@/Shared/Components/ServiceOrder/PhotoUploadZone.vue';
import { ServiceOrderStatusLabels, ServiceOrderStatusColors } from '@/Data/serviceOrderStatuses.js';
import { getAvailableTransitions, getTransitionLabel } from '@/Composables/useServiceOrderTransitions.js';
import { addServiceOrderItem, removeServiceOrderItem, updateDiagnosis, changeServiceOrderStatus, requestNewApproval, cancelServiceOrder } from '@/services/serviceOrderService.js';
import { fetchServices } from '@/services/serviceService.js';
import { useToast } from '@/Shared/composables/useToast.js';

const props = defineProps({
  serviceOrder: {
    type: Object,
    required: true,
  },
});

const activeTab = ref('diagnostico');
const showMobileInfo = ref(false);


const tabs = [
  { key: 'diagnostico',    label: 'Diagnóstico',   icon: 'ki-filled ki-setting-2' },
  { key: 'itens',          label: 'Itens',          icon: 'ki-filled ki-basket'    },
  { key: 'linha-do-tempo', label: 'Linha do Tempo', icon: 'ki-filled ki-time'      },
  { key: 'financeiro',     label: 'Financeiro',     icon: 'ki-filled ki-dollar'    },
];

// --- Itens ---
const toast = useToast();
const items = computed(() => props.serviceOrder.items || []);
const newItem = ref(null);
const savingItem = ref(false);
const removingItemId = ref(null);
const itemError = ref(null);

// --- Diagnóstico ---
const diagnosisInput = ref(props.serviceOrder?.technical_diagnosis ?? '');

// --- Transições ---
const transitioning = ref(false);
const cancelModalOpen = ref(false);
const cancelReason = ref('');
const showActionsDropdown = ref(false);

// Dropdown de tipo de item
const showTypeDropdown = ref(false);
const itemTypeOptions = [
  { value: 'service', label: 'Serviço' },
  { value: 'part',    label: 'Peça'    },
];

function selectItemType(value) {
  onNewItemTypeChange();
  newItem.value.type = value;
  showTypeDropdown.value = false;
}

// Dropdown de busca de serviços
const newItemDescInput = ref(null);
const filteredServices = ref([]);
const showServiceDropdown = ref(false);
let searchTimer = null;

const totalItems = computed(() =>
  items.value.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0)
);

const canSaveNewItem = computed(() =>
  newItem.value &&
  newItem.value.description?.trim() &&
  newItem.value.quantity > 0 &&
  newItem.value.unit_price >= 0
);

const serviceDropdownStyle = computed(() => {
  if (!newItemDescInput.value) return { display: 'none' };
  const rect = newItemDescInput.value.getBoundingClientRect();
  return {
    position: 'fixed',
    top: `${rect.bottom + 4}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    zIndex: 99999,
  };
});

function startAddItem() {
  newItem.value = { type: 'service', service_id: null, description: '', quantity: 1, unit_price: 0 };
}

function getTrackingContext() {
  return {
    serviceOrderNumber: props.serviceOrder.order_number,
    serviceOrderStatus: props.serviceOrder.status,
  };
}

function cancelAddItem() {
  newItem.value = null;
  filteredServices.value = [];
  showServiceDropdown.value = false;
}

async function confirmAddItem() {
  if (!canSaveNewItem.value) return;
  savingItem.value = true;
  itemError.value = null;
  const result = await addServiceOrderItem(props.serviceOrder.id, newItem.value, getTrackingContext());
  savingItem.value = false;
  if (result.success) {
    newItem.value = null;
    filteredServices.value = [];
    toast.success('Item adicionado com sucesso!');
    router.reload();
  } else {
    itemError.value = result.error || 'Erro ao adicionar item.';
    toast.error(result.error || 'Erro ao adicionar item.');
  }
}

async function handleRemoveItem(itemId) {
  removingItemId.value = itemId;
  itemError.value = null;
  const result = await removeServiceOrderItem(props.serviceOrder.id, itemId, getTrackingContext());
  removingItemId.value = null;
  if (result.success) {
    toast.success('Item removido com sucesso!');
    router.reload();
  } else {
    itemError.value = result.error || 'Erro ao remover item.';
    toast.error(result.error || 'Erro ao remover item.');
  }
}

function onNewItemTypeChange() {
  if (newItem.value) {
    newItem.value.service_id = null;
    newItem.value.description = '';
  }
  filteredServices.value = [];
  showServiceDropdown.value = false;
}

function onNewItemSearch(event) {
  if (newItem.value?.type !== 'service') return;
  const search = event.target.value;
  if (search.length < 2) {
    filteredServices.value = [];
    showServiceDropdown.value = false;
    return;
  }
  clearTimeout(searchTimer);
  searchTimer = setTimeout(async () => {
    const result = await fetchServices({ search, perPage: 10 });
    filteredServices.value = result.items || [];
    showServiceDropdown.value = filteredServices.value.length > 0;
  }, 300);
}

function onNewItemFocus(event) {
  if (newItem.value?.type !== 'service' || event.target.value.length < 2) return;
  onNewItemSearch(event);
}

function onNewItemBlur() {
  setTimeout(() => { showServiceDropdown.value = false; }, 200);
}

function selectService(service) {
  newItem.value.service_id = service.id;
  newItem.value.description = service.name;
  newItem.value.unit_price = parseFloat(service.base_price) || 0;
  filteredServices.value = [];
  showServiceDropdown.value = false;
}

// --- Diagnóstico ---
async function handleDiagnosisBlur() {
  const current = props.serviceOrder?.technical_diagnosis ?? '';
  if (diagnosisInput.value === current) return;

  // Se o diagnóstico foi apagado (vazio), apenas recarrega sem chamar o endpoint
  if (!diagnosisInput.value || diagnosisInput.value.trim() === '') {
    router.reload();
    return;
  }

  const result = await updateDiagnosis(props.serviceOrder.id, diagnosisInput.value, getTrackingContext());
  if (result.success) {
    toast.success('Diagnóstico técnico atualizado com sucesso!');
    router.reload();
  } else {
    toast.error(result.error || 'Erro ao salvar diagnóstico.');
  }
}

// --- Fotos ---
function handlePhotoUploaded(photo) {
  toast.success('Foto enviada com sucesso!');
  router.reload();
}

function handlePhotoDeleted(photoId) {
  toast.success('Foto removida com sucesso!');
  router.reload();
}

function handlePhotoError(error) {
  toast.error(error || 'Erro ao processar foto.');
}

// --- Transições ---
const availableTransitions = computed(() => getAvailableTransitions(props.serviceOrder?.status));

function getPrimaryAction(status) {
  const transitions = {
    'draft': 'waiting_approval',
    'waiting_approval': 'approved',
    'approved': 'in_progress',
    'in_progress': 'waiting_payment',
  };
  return transitions[status];
}

function getSecondaryActions(status) {
  const all = availableTransitions.value;
  const primary = getPrimaryAction(status);
  return all.filter(action => action !== primary);
}

async function handleTransition(toStatus) {
  let result;

  // Validação frontend para "Enviar para Aprovação" (draft → waiting_approval)
  if (props.serviceOrder.status === 'draft' && toStatus === 'waiting_approval') {
    const hasItems = props.serviceOrder.items && props.serviceOrder.items.length > 0;
    const hasDiagnosis = props.serviceOrder.technical_diagnosis?.trim();

    if (!hasItems && !hasDiagnosis) {
      activeTab.value = 'diagnostico';
      toast.error('Preencha o diagnóstico e adicione pelo menos um item antes de enviar para aprovação.');
      return;
    }

    if (!hasDiagnosis) {
      activeTab.value = 'diagnostico';
      toast.error('Preencha o diagnóstico antes de enviar para aprovação.');
      return;
    }

    if (!hasItems) {
      activeTab.value = 'itens';
      toast.error('Adicione pelo menos um item antes de enviar para aprovação.');
      return;
    }
  }

  // "Solicitar Nova Aprovação": valida diagnóstico e envia itens atuais para evitar deleção no backend
  if (props.serviceOrder.status === 'in_progress' && toStatus === 'waiting_approval') {
    if (!props.serviceOrder.technical_diagnosis?.trim()) {
      activeTab.value = 'diagnostico';
      toast.error('Atualize o diagnóstico técnico antes de solicitar nova aprovação.');
      return;
    }
    transitioning.value = true;
    result = await requestNewApproval(props.serviceOrder.id, {
      diagnosis: props.serviceOrder.technical_diagnosis,
      items: props.serviceOrder.items.map(i => ({
        id: i.id, type: i.type, description: i.description,
        quantity: i.quantity, unit_price: i.unit_price,
        service_id: i.service_id ?? null,
      }))
    }, getTrackingContext());
  } else {
    transitioning.value = true;
    result = await changeServiceOrderStatus(props.serviceOrder.id, toStatus, props.serviceOrder.status, getTrackingContext());
  }

  transitioning.value = false;

  if (result.success) {
    toast.success('Status alterado com sucesso!');
    router.reload();
  } else {
    // Feedback específico baseado no erro
    if (result.error?.toLowerCase().includes('item') || result.error?.toLowerCase().includes('vazio')) {
      activeTab.value = 'itens';
    } else if (result.error?.toLowerCase().includes('diagnóstico') || result.error?.toLowerCase().includes('diagnostico')) {
      activeTab.value = 'diagnostico';
    }
    toast.error(result.error || 'Erro ao alterar status.');
  }
}

async function handleCancel() {
  if (!cancelReason.value.trim()) return;
  const result = await cancelServiceOrder(props.serviceOrder.id, cancelReason.value, getTrackingContext());
  if (result.success) {
    cancelModalOpen.value = false;
    cancelReason.value = '';
    toast.success('OS cancelada com sucesso!');
    router.reload();
  } else {
    toast.error(result.error || 'Erro ao cancelar OS.');
  }
}

function handleCancelFromFinanceiro() {
  cancelModalOpen.value = true;
}

// Fechar dropdowns ao clicar fora
function handleClickOutside(event) {
  if (showActionsDropdown.value) showActionsDropdown.value = false;
  if (showTypeDropdown.value) showTypeDropdown.value = false;
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Sincronizar diagnosisInput quando serviceOrder muda
watch(
  () => props.serviceOrder?.technical_diagnosis,
  (newDiagnosis) => {
    diagnosisInput.value = newDiagnosis ?? '';
  },
  { immediate: true }
);

// --- Breadcrumbs / status ---
const totalPaid = computed(() => parseFloat(props.serviceOrder?.paid_amount) || 0);

const breadcrumbs = computed(() => [
  { label: 'Ordens de Serviço', href: '/service-orders' },
  { label: props.serviceOrder ? `OS-${props.serviceOrder.order_number}` : 'Detalhes' },
]);

const vehicleLabel = computed(() => {
  const v = props.serviceOrder?.vehicle;
  if (!v) return '—';
  return [v.brand, v.model, v.year].filter(Boolean).join(' ');
});

const statusLabel = computed(() =>
  ServiceOrderStatusLabels[props.serviceOrder?.status] ?? props.serviceOrder?.status ?? ''
);

const statusColor = computed(() =>
  ServiceOrderStatusColors[props.serviceOrder?.status] ?? 'bg-gray-100 text-gray-700'
);

function goBack() {
  router.visit('/service-orders');
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

function formatMileage(value) {
  return new Intl.NumberFormat('pt-BR').format(value) + ' km';
}
</script>
