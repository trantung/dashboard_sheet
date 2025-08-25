<template>
    <div class="min-vh-100 bg-light d-flex align-items-center justify-content-center">
        <div class="text-center">
            <div class="spinner-border text-success mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Processing Google Sign-In...</h5>
            <p class="text-muted">Please wait while we complete your authentication.</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

onMounted(async () => {
    const code = route.query.code
    const error = route.query.error

    if (error) {
        console.error('Google OAuth error:', error)
        router.push('/login?error=google_auth_failed')
        return
    }

    if (code) {
        // Send authorization code to backend
        const result = await authStore.loginWithGoogle(code)

        if (result.success) {
            router.push('/dashboard')
        } else {
            router.push('/login?error=google_auth_failed')
        }
    } else {
        router.push('/login?error=invalid_callback')
    }
})
</script>