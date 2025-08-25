<template>
    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-4 mb-md-5">
                <h3 class="fw-bold mb-2">Everything you need to build a website</h3>
                <p class="text-muted mx-auto" style="max-width: 720px;">
                    Powerful features, sensible defaults, delightful UX.
                </p>
            </div>

            <div class="row g-3 g-md-4">
                <div v-for="feature in features" :key="feature.title" class="col-12 col-md-6 col-lg-4">
                    <div class="h-100 border rounded bg-white p-3 p-md-4">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <span class="badge bg-success bg-opacity-10 text-success p-2">
                                    <i :class="['bi', feature.icon]"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ feature.title }}</h6>
                                <p class="text-muted small mb-0">{{ feature.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-outline-secondary btn-sm">View all features</button>
            </div>
        </div>
    </section>
</template>

<script setup>
const features = [
    { icon: 'bi-palette', title: '10+ design themes', desc: 'Pick a theme and customize quickly.' },
    { icon: 'bi-columns-gap', title: 'Page builder', desc: 'Create pages from Google Docs.' },
    { icon: 'bi-rocket', title: 'SEO & Performance', desc: 'Meta tags, sitemap, lightning-fast CDN.' },
    { icon: 'bi-search', title: 'Search & Filters', desc: 'Help visitors find the right items.' },
    { icon: 'bi-lock', title: 'Password protection', desc: 'Restrict access with per-site passwords.' },
    { icon: 'bi-gear', title: 'Custom code', desc: 'Inject JS/CSS anywhere: head, body open/close.' },
]
</script>


