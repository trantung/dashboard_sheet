<template>
    <section id="showcase" class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-4 mb-md-5">
                <h3 class="fw-bold">Built with SheetExpress</h3>
                <p class="text-muted">Explore sites created by our community</p>
            </div>

            <div class="row g-3 g-md-4">
                <div v-for="site in sites" :key="site.title" class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="ratio ratio-4x3 bg-light"></div>
                        <div class="card-body">
                            <h6 class="card-title mb-1 text-truncate">{{ site.title }}</h6>
                            <small class="text-muted text-truncate d-block">{{ site.category }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const sites = [
    { title: 'Crypto Directory', category: 'Directory' },
    { title: 'Jobs Hub', category: 'Job board' },
    { title: 'Startup Showcase', category: 'Collection' },
    { title: 'Travel Deals', category: 'Listing' },
]
</script>


