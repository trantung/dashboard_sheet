<template>
    <div id="app">
        <router-view />
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth.js'

const authStore = useAuthStore()

// Check if user is authenticated on app load
onMounted(() => {
    authStore.checkAuth()
})
</script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.hero-gradient {
    background: linear-gradient(135deg, #e8f5e8 0%, #e3f2fd 100%);
}

.text-decoration-underline-custom {
    text-decoration: underline;
    text-decoration-color: #28a745;
    text-decoration-thickness: 3px;
    text-underline-offset: 8px;
}
</style>