<template>
    <div class="modal fade" :class="{ show: show }" :style="{ display: show ? 'block' : 'none' }" tabindex="-1"
        @click.self="closeModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-2">
                    <div class="w-100 text-center">
                        <div class="bg-primary/10 bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-receipt text-success fs-4"></i>
                        </div>
                        <h5 class="modal-title">#{{ order?.id }}</h5>
                    </div>
                    <button type="button" class="btn-close position-absolute" style="top: 15px; right: 15px;"
                        @click="closeModal"></button>
                </div>
                <div class="modal-body px-4" v-if="order">
                    <!-- Customer information section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Name</label>
                                <p class="mb-0">{{ order.name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email</label>
                                <p class="mb-0">{{ order.email }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Phone</label>
                                <p class="mb-0">{{ order.phone }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Address</label>
                                <p class="mb-0">{{ order.address }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Note</label>
                                <p class="mb-0">{{ order.note || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order summary section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>${{ order.subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount coupon</span>
                                <span>{{ order.discount_coupon || '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount</span>
                                <span>${{ order.discount.toFixed(2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span>${{ order.shipping.toFixed(2) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span>${{ order.total.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Products table section -->
                    <div>
                        <h6 class="fw-bold mb-3">Products</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>SKU</th>
                                        <th>NAME</th>
                                        <th>QUANTITY</th>
                                        <th>PRICE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in order.products" :key="product.sku">
                                        <td>{{ product.sku }}</td>
                                        <td>{{ product.name }}</td>
                                        <td>{{ product.quantity }}</td>
                                        <td>${{ product.price.toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="show" class="modal-backdrop fade show" @click="closeModal"></div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    order: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close'])

const closeModal = () => {
    emit('close')
}
</script>

<style scoped>
.bg-primary\/10 {
    background-color: rgba(15, 157, 96, .1);
}

.modal.show {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>
