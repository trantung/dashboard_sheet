<template>
    <header class="bg-white border-bottom px-2 px-md-4 py-2 py-md-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- Mobile Menu Toggle Button -->
                <button 
                    @click="$emit('toggle-sidebar')"
                    class="btn btn-outline-secondary btn-sm me-2 d-md-none"
                    :class="{ 'active': sidebarOpen }"
                >
                    <i class="bi bi-list fs-5"></i>
                </button>
                
                <i class="bi bi-brightness-high me-2 me-md-3 text-muted"></i>
            </div>
            
            <div class="d-flex align-items-center gap-1 gap-md-2">
                <button @click="$emit('show-feedback')" class="btn btn-outline-secondary btn-sm">
                    <span class="d-none d-sm-inline">Feedback</span>
                    <i class="bi bi-chat-dots d-sm-none"></i>
                </button>
                <button @click="$emit('change-tab', 'pricing')" class="btn btn-success btn-sm">
                    <span class="d-none d-sm-inline">Upgrade →</span>
                    <i class="bi bi-arrow-up-circle d-sm-none"></i>
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    sidebarOpen: {
        type: Boolean,
        default: false
    }
})

const emits = defineEmits(['show-feedback', 'change-tab', 'toggle-sidebar'])
</script>

<style scoped>
.btn.active {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn.active:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

/* Mobile menu button animation */
@media (max-width: 767.98px) {
    .btn-outline-secondary {
        transition: all 0.3s ease;
    }
    
    .btn-outline-secondary:hover {
        transform: scale(1.05);
    }
}
</style>