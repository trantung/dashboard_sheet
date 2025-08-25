<template>
    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-4 mb-md-5">
                <h3 class="fw-bold">Our growing wall of love</h3>
                <p class="text-muted">What our users say</p>
            </div>

            <div class="row g-3 g-md-4">
                <div v-for="(t,i) in testimonials" :key="i" class="col-12 col-md-6 col-lg-4">
                    <div class="h-100 p-3 p-md-4 bg-white border rounded">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success me-2" style="width: 36px; height: 36px;"></div>
                            <div>
                                <strong class="d-block">{{ t.name }}</strong>
                                <small class="text-muted">{{ t.role }}</small>
                            </div>
                        </div>
                        <p class="mb-0 text-muted">"{{ t.text }}"</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const testimonials = [
    { name: 'Alex', role: 'Founder', text: 'Sheetany let us launch a content site in a weekend.' },
    { name: 'Linh', role: 'Marketer', text: 'Updating content from Sheets is a game changer.' },
    { name: 'Sam', role: 'Creator', text: 'I built a portfolio and newsletter hub with zero code.' },
]
</script>


