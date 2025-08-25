<template>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i :class="['bi', iconClass]" :style="{ color: iconColor, fontSize: '2rem' }"></i>
                    </div>
                    <h5 class="modal-title mb-2">{{ title }}</h5>
                    <p class="text-muted mb-4">{{ message }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button @click="onCancel" class="btn btn-outline-secondary px-4">
                            {{ cancelText }}
                        </button>
                        <button @click="onConfirm" :class="['btn px-4', confirmButtonClass]">
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Confirm Action'
    },
    message: {
        type: String,
        default: 'Are you sure you want to perform this action?'
    },
    type: {
        type: String,
        default: 'danger', // danger, warning, info, success
        validator: (value) => ['danger', 'warning', 'info', 'success'].includes(value)
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    },
    confirmText: {
        type: String,
        default: 'Confirm'
    }
})

const emit = defineEmits(['cancel', 'confirm'])

const onCancel = () => {
    emit('cancel')
}

const onConfirm = () => {
    emit('confirm')
}

console.log(props.show);

// Computed properties for styling based on type
const iconClass = (() => {
    switch (props.type) {
        case 'danger': return 'bi-trash text-danger'
        case 'warning': return 'bi-exclamation-triangle text-warning'
        case 'info': return 'bi-info-circle text-info'
        case 'success': return 'bi-check-circle text-success'
        default: return 'bi-question-circle text-primary'
    }
})()

const iconColor = (() => {
    switch (props.type) {
        case 'danger': return '#dc3545'
        case 'warning': return '#ffc107'
        case 'info': return '#0dcaf0'
        case 'success': return '#198754'
        default: return '#0d6efd'
    }
})()

const confirmButtonClass = (() => {
    switch (props.type) {
        case 'danger': return 'btn-danger'
        case 'warning': return 'btn-warning'
        case 'info': return 'btn-info'
        case 'success': return 'btn-success'
        default: return 'btn-primary'
    }
})()
</script>
