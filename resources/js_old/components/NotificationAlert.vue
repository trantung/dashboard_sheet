<template>
    <div class="notification-container position-fixed" style="top: 20px; right: 20px; z-index: 1050; max-width: 400px;">
        <transition-group name="notification" tag="div">
            <div v-for="notification in notifications" :key="notification.id" 
                 :class="['alert', `alert-${notification.type}`, 'alert-dismissible', 'd-flex', 'align-items-center', 'mb-3', 'shadow-sm']"
                 style="border-radius: 8px;">
                <i :class="getIcon(notification.type)" class="me-2"></i>
                <div class="flex-grow-1">
                    <strong v-if="notification.title">{{ notification.title }}</strong>
                    <div v-if="notification.title && notification.message" class="small">{{ notification.message }}</div>
                    <div v-else>{{ notification.message }}</div>
                </div>
                <button type="button" class="btn-close" @click="removeNotification(notification.id)"></button>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const notifications = ref([])
let notificationId = 0

const getIcon = (type) => {
    const icons = {
        success: 'bi bi-check-circle-fill',
        error: 'bi bi-exclamation-triangle-fill',
        warning: 'bi bi-exclamation-triangle-fill',
        info: 'bi bi-info-circle-fill'
    }
    return icons[type] || icons.info
}

const addNotification = (type, message, title = null, options = {}) => {
    const id = ++notificationId
    const notification = {
        id,
        type,
        message,
        title,
        ...options
    }
    
    notifications.value.push(notification)
    
    // Auto remove after duration (default 5 seconds for success/info/warning, never for error)
    const duration = options.duration !== undefined ? options.duration : (type === 'error' ? 0 : 5000)
    if (duration > 0) {
        setTimeout(() => {
            removeNotification(id)
        }, duration)
    }
    
    return id
}

const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
        notifications.value.splice(index, 1)
    }
}

const clearNotifications = () => {
    notifications.value = []
}

const showSuccess = (message, title = null, options = {}) => {
    return addNotification('success', message, title, options)
}

const showError = (message, title = null, options = {}) => {
    return addNotification('error', message, title, options)
}

const showWarning = (message, title = null, options = {}) => {
    return addNotification('warning', message, title, options)
}

const showInfo = (message, title = null, options = {}) => {
    return addNotification('info', message, title, options)
}

defineExpose({
    showSuccess,
    showError,
    showWarning,
    showInfo,
    clearNotifications,
    removeNotification
})
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.3s ease;
}

.notification-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.notification-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.notification-move {
    transition: transform 0.3s ease;
}

.alert {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.alert-success {
    background-color: #d1e7dd;
    color: #0f5132;
}

.alert-error,
.alert-danger {
    background-color: #f8d7da;
    color: #842029;
}

.alert-warning {
    background-color: #fff3cd;
    color: #664d03;
}

.alert-info {
    background-color: #d1ecf1;
    color: #055160;
}
</style>