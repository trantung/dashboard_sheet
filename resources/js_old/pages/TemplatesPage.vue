<template>
    <GuestLayout>
        <!-- Hero -->
        <section class="py-5 hero-gradient border-bottom">
            <div class="container py-4 text-center">
                <h1 class="fw-bold mb-3">Perfect Website Templates</h1>
                <p class="text-muted mx-auto" style="max-width: 720px;">
                    Modern, mobile‑friendly, and feature‑rich templates to kickstart your site. Connect Google Sheets and publish in minutes.
                </p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button class="btn btn-success">Start with a template</button>
                    <router-link class="btn btn-outline-secondary" to="/pricing">View pricing</router-link>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <section class="bg-white py-3 border-bottom">
            <div class="container d-flex flex-wrap gap-2 justify-content-center">
                <button v-for="f in filters" :key="f.value" class="btn btn-sm"
                        :class="activeFilter===f.value? 'btn-success' : 'btn-outline-secondary'"
                        @click="activeFilter=f.value">
                    {{ f.label }}
                </button>
            </div>
        </section>

        <!-- Grid -->
        <section class="py-4 py-md-5">
            <div class="container">
                <div class="row g-3 g-md-4">
                    <div v-for="tpl in filteredTemplates" :key="tpl.id" class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="border-top border-5" :style="{ borderColor: tpl.color }"></div>
                            <div class="ratio ratio-16x9 bg-light"></div>
                            <div class="card-body">
                                <h5 class="card-title mb-1">{{ tpl.title }}</h5>
                                <small class="text-muted d-block mb-2">{{ tpl.category }}</small>
                                <p class="text-muted small">{{ tpl.desc }}</p>
                                <div class="d-flex gap-2">
                                    <router-link class="btn btn-success btn-sm" :to="`/website/add/${tpl.id}`">Use template</router-link>
                                    <button class="btn btn-outline-secondary btn-sm">Live demo</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <CTASection />
        <FooterSection />
    </GuestLayout>
    
</template>

<script setup>
import { computed, ref } from 'vue'
import GuestLayout from '../layouts/GuestLayout.vue'
import CTASection from '../components/CTASection.vue'
import FooterSection from '../components/FooterSection.vue'

const filters = [
    { value: 'all', label: 'All' },
    { value: 'directory', label: 'Directory' },
    { value: 'blog', label: 'Blog' },
    { value: 'jobs', label: 'Job board' },
    { value: 'ecom', label: 'E‑commerce' },
]

const activeFilter = ref('all')

const templates = ref([
    { id: 1, title: 'Collection/Directory', category: 'Directory', type: 'directory', color: '#2ecc71', desc: 'Browse and filter items from a Sheet.' },
    { id: 2, title: 'Jobs Directory', category: 'Job board', type: 'jobs', color: '#9b59b6', desc: 'Post jobs, categories, and filter by tags.' },
    { id: 3, title: 'Landing Page', category: 'Landing', type: 'landing', color: '#27ae60', desc: 'Simple, clean marketing page.' },
    { id: 4, title: 'Feature Grid', category: 'Marketing', type: 'landing', color: '#f39c12', desc: 'Show off features with icons and cards.' },
    { id: 5, title: 'Blog', category: 'Blog', type: 'blog', color: '#3498db', desc: 'Publish posts from your spreadsheet.' },
    { id: 6, title: 'Portfolio', category: 'Portfolio', type: 'landing', color: '#8e44ad', desc: 'Showcase projects with categories.' },
    { id: 7, title: 'E‑commerce Directory', category: 'Catalog', type: 'ecom', color: '#2980b9', desc: 'Catalog products with filters, not checkout.' },
    { id: 8, title: 'SaaS Docs', category: 'Docs', type: 'landing', color: '#16a085', desc: 'Explain your product with doc sections.' },
])

const filteredTemplates = computed(() => {
    if (activeFilter.value === 'all') return templates.value
    return templates.value.filter(t => t.type === activeFilter.value)
})
</script>


