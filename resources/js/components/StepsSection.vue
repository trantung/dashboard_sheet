<template>
    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-4 mb-md-5">
                <h3 class="fw-bold">Google Sheets to Website in 4 Easy Steps</h3>
                <p class="text-muted">Pick a template, connect your sheet, customize, and publish.</p>
            </div>

            <div class="row g-3 g-md-4 text-center">
                <div v-for="(step, i) in steps" :key="i" class="col-12 col-md-6 col-lg-3">
                    <div class="h-100 p-3 p-md-4 border rounded bg-light">
                        <div class="display-6 fw-bold text-success mb-2">{{ i + 1 }}</div>
                        <h6 class="mb-1">{{ step.title }}</h6>
                        <p class="text-muted small mb-0">{{ step.desc }}</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-success">Try free now</button>
            </div>
        </div>
    </section>
</template>

<script setup>
const steps = [
    { title: 'Pick a template', desc: 'Start fast with a ready-made layout.' },
    { title: 'Data mapping', desc: 'Map columns to website fields.' },
    { title: 'Choose your domain', desc: 'Use subdomain or connect your own.' },
    { title: 'Publish your website', desc: 'Go live in minutes, not weeks.' }
]
</script>


