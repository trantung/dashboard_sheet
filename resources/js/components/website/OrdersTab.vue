<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Orders</h4>
                <p class="text-muted mb-0">List of orders on your website</p>
            </div>
            <button class="btn btn-outline-secondary" @click="handleExport" :disabled="isLoading">
                <i class="bi bi-download me-2"></i>
                Download
            </button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div v-if="isLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Loading orders...</p>
                </div>

                <div v-else-if="errorMessage" class="text-center py-5">
                    <i class="bi bi-exclamation-triangle fs-1 text-danger mb-3"></i>
                    <h5 class="mb-2">Error Loading Orders</h5>
                    <p class="text-muted">{{ errorMessage }}</p>
                    <button class="btn btn-primary" @click="loadOrders">Try Again</button>
                </div>

                <div v-else-if="ordersList.length > 0" class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">NAME</th>
                                <th class="px-4 py-3">EMAIL</th>
                                <th class="px-4 py-3">SUBTOTAL</th>
                                <th class="px-4 py-3">TOTAL</th>
                                <th class="px-4 py-3">STATUS</th>
                                <th class="px-4 py-3">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in ordersList" :key="order.id">
                                <td class="px-4 py-3">{{ order.order_no }}</td>
                                <td class="px-4 py-3">{{ order.name }}</td>
                                <td class="px-4 py-3">{{ order.email }}</td>
                                <td class="px-4 py-3">${{ order.subtotal.toFixed(2) }}</td>
                                <td class="px-4 py-3">${{ order.total.toFixed(2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-primary">{{ order.status }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <button class="btn btn-outline-success btn-sm" @click="viewDetail(order.id)">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-5">
                    <i class="bi bi-bag fs-1 text-muted mb-3"></i>
                    <h5 class="mb-2">No Orders Found</h5>
                    <p class="text-muted">There are no orders to display.</p>
                </div>
            </div>

            <!-- Added pagination controls -->
            <div v-if="pagination && pagination.last_page > 1" class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                <button class="page-link" @click="changePage(pagination.current_page - 1)"
                                    :disabled="pagination.current_page === 1">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            </li>

                            <li v-for="page in visiblePages" :key="page" class="page-item"
                                :class="{ active: page === pagination.current_page }">
                                <button class="page-link" @click="changePage(page)">{{ page }}</button>
                            </li>

                            <li class="page-item"
                                :class="{ disabled: pagination.current_page === pagination.last_page }">
                                <button class="page-link" @click="changePage(pagination.current_page + 1)"
                                    :disabled="pagination.current_page === pagination.last_page">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <OrderDetail :show="showModal" :order="currentOrder" @close="closeModal" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import OrderDetail from './OrderDetail.vue'

// Route and reactive state
const route = useRoute()
const siteId = ref(route.params.id)
const ordersList = ref([])
const currentOrder = ref(null)
const isLoading = ref(false)
const errorMessage = ref(null)
const showModal = ref(false)
const pagination = ref(null)
const currentPage = ref(1)
const perPage = ref(10)

const visiblePages = computed(() => {
    if (!pagination.value) return []

    const current = pagination.value.current_page
    const last = pagination.value.last_page
    const pages = []

    // Show max 5 pages around current page
    const start = Math.max(1, current - 2)
    const end = Math.min(last, current + 2)

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
})

// Lifecycle
onMounted(() => {
    loadOrders()
})

// Methods
const loadOrders = async (page = 1) => {
    isLoading.value = true
    errorMessage.value = null
    currentPage.value = page

    try {
        const response = await axios.get(ziggyRoute('api.orders.index', { site: siteId.value }), {
            params: {
                page: page,
                per_page: perPage.value
            }
        })

        if (response.data.success) {
            ordersList.value = response.data.data
            pagination.value = response.data.pagination
        } else {
            errorMessage.value = response.data.message || 'Failed to fetch orders'
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Network error occurred'
    } finally {
        isLoading.value = false
    }
}

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page && page !== currentPage.value) {
        loadOrders(page)
    }
}

const viewDetail = async (orderId) => {
    try {
        const response = await axios.get(ziggyRoute('api.orders.show', {
            site: siteId.value,
            id: orderId
        }))

        if (response.data.success) {
            currentOrder.value = response.data.data
            showModal.value = true
        }
    } catch (err) {
        console.error('Error fetching order details:', err)
    }
}

const closeModal = () => {
    showModal.value = false
    currentOrder.value = null
}

const handleExport = async () => {
    try {
        const response = await axios.get(ziggyRoute('api.orders.export', { site: siteId.value }), {
            responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.download = 'orders_export.xlsx'
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
    } catch (err) {
        console.error('Error exporting orders:', err)
    }
}
</script>

<style scoped>
.table th {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
}

.btn-outline-success {
    border-color: #28a745;
    color: #28a745;
}

.btn-outline-success:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

/* Added pagination styles */
.pagination-sm .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.page-link {
    color: #6c757d;
    border-color: #dee2e6;
}

.page-link:hover {
    color: #495057;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>