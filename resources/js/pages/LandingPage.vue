<template>
    <GuestLayout>
        <HeroSection />
        <LogoStripSection />
        <PreviewSection />
        <WhySection />
        <EverythingSection />
        <StepsSection />
        <ShowcaseSection />
        <TestimonialsSection />
        <FAQSection />
        <CTASection />
        <!-- Keep legacy section if needed -->
        <!-- <FeaturesSection /> -->
        <FooterSection />
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '../layouts/GuestLayout.vue'
import HeroSection from '../components/HeroSection.vue'
import LogoStripSection from '../components/LogoStripSection.vue'
import PreviewSection from '../components/PreviewSection.vue'
import WhySection from '../components/WhySection.vue'
import EverythingSection from '../components/EverythingSection.vue'
import StepsSection from '../components/StepsSection.vue'
import ShowcaseSection from '../components/ShowcaseSection.vue'
import TestimonialsSection from '../components/TestimonialsSection.vue'
import FAQSection from '../components/FAQSection.vue'
import CTASection from '../components/CTASection.vue'
import FooterSection from '../components/FooterSection.vue'
</script>