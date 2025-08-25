<template>
    <div class="container-fluid px-2 px-md-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2">
            <div>
                <h4 class="mb-1 fs-5 fs-md-4">Integrations</h4>
                <p class="text-muted mb-0 small">Add third party integrations to your website</p>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <!-- Google Analytics -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 border-1 integration-card" @click="openTrialLimitDialog('google-analytics')">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-start mb-3">
                            <img :src="googleAnalytics" alt="Google Analytics" class="me-3 flex-shrink-0" style="width: 40px; height: 40px;" />
                            <div class="flex-fill min-width-0">
                                <h6 class="mb-1 fw-semibold text-truncate">Google Analytics</h6>
                            </div>
                        </div>
                        <p class="card-text text-muted small mb-0">
                            Get traffic insights from your website in your Google Analytics dashboard.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Google Adsense -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 border-1 integration-card" @click="openTrialLimitDialog('google-adsense')">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-start mb-3">
                            <img :src="googleAdsense" alt="Google Adsense" class="me-3 flex-shrink-0" style="width: 40px; height: 40px;" />
                            <div class="flex-fill min-width-0">
                                <h6 class="mb-1 fw-semibold text-truncate">Google Adsense</h6>
                            </div>
                        </div>
                        <p class="card-text text-muted small mb-0">
                            Monetize your website with Google AdSense and earn revenue from ads.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Crisp Chat -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 border-1 integration-card" @click="openTrialLimitDialog('crisp-chat')">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-start mb-3">
                            <img :src="crispLogo" alt="Crisp Chat" class="me-3 flex-shrink-0" style="width: 40px; height: 40px;" />
                            <div class="flex-fill min-width-0">
                                <h6 class="mb-1 fw-semibold text-truncate">Crisp Chat</h6>
                            </div>
                        </div>
                        <p class="card-text text-muted small mb-0">
                            Engage with your visitors in real-time using Crisp Chat.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Google Tag Manager -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 border-1 integration-card" @click="openTrialLimitDialog('google-tag-manager')">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-start mb-3">
                            <img :src="googleTag" alt="Google Tag Manager" class="me-3 flex-shrink-0" style="width: 40px; height: 40px;" />
                            <div class="flex-fill min-width-0">
                                <h6 class="mb-1 fw-semibold text-truncate">Google Tag Manager</h6>
                            </div>
                        </div>
                        <p class="card-text text-muted small mb-0">
                            Easily track and manage audience interactions on your website.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mailchimp -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 border-1 integration-card" @click="openTrialLimitDialog('mailchimp')">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-start mb-3">
                            <img :src="mailchimpLogo" alt="Mailchimp" class="me-3 flex-shrink-0" style="width: 40px; height: 40px;" />
                            <div class="flex-fill min-width-0">
                                <h6 class="mb-1 fw-semibold text-truncate d-flex align-items-center gap-2">
                                    <span>Mailchimp</span>
                                    <span class="badge bg-secondary small">Coming soon</span>
                                </h6>
                            </div>
                        </div>
                        <p class="card-text text-muted small mb-0">
                            Grow your audience and manage email marketing campaigns with ease.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Disqus -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 border-1 integration-card" @click="openTrialLimitDialog('disqus')">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-start mb-3">
                            <img :src="disqusLogo" alt="Disqus" class="me-3 flex-shrink-0" style="width: 40px; height: 40px;" />
                            <div class="flex-fill min-width-0">
                                <h6 class="mb-1 fw-semibold text-truncate d-flex align-items-center gap-2">
                                    <span>Disqus</span>
                                    <span class="badge bg-secondary small">Coming soon</span>
                                </h6>
                            </div>
                        </div>
                        <p class="card-text text-muted small mb-0">
                            Enable discussions and user engagement on your blog or website.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integration Dialog -->
        <!-- <TrialLimitDialog :show="showDialog" :integration="selectedIntegration" @close="closeTrialLimitDialog" @change-tab="setActiveTab" /> -->
         <TrialLimitDialog
            :show="showDialog"
            :integration="selectedIntegration"
            :showAlert="true"
            :closable="true"
            alertMessage="Paid feature 😊"
            @close="closeTrialLimitDialog"
            @change-tab="setActiveTab"
        />
    </div>
</template>

<script>
import TrialLimitDialog from '../TrialLimitDialog.vue';
import googleAnalytics from '@images/google-analytics.png'
import googleAdsense from '@images/google-adsense.png'
import crispLogo from '@images/crisp_logo.svg'
import googleTag from '@images/google-tag.png'
import mailchimpLogo from '@images/mailchimp.png'
import disqusLogo from '@images/disqus.svg'

export default {
    name: 'IntegrationsTab',
    components: {
        TrialLimitDialog
    },
    data() {
        return {
            googleAnalytics,
            googleAdsense,
            crispLogo,
            googleTag,
            mailchimpLogo,
            disqusLogo,
            showDialog: false,
            selectedIntegration: null
        }
    },
    methods: {
        openTrialLimitDialog(integrationId) {
            this.selectedIntegration = integrationId;
            this.showDialog = true;
        },
        closeTrialLimitDialog() {
            this.showDialog = false;
            this.selectedIntegration = null;
        },
        setActiveTab() {
           this.$emit('change-tab-dashboard', 'pricing')
        }
    }
}
</script>

<style scoped>
.integration-card {
    cursor: pointer;
    transition: all 0.2s ease;
}

.integration-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.min-width-0 {
    min-width: 0;
}

/* Mobile optimizations */
@media (max-width: 575.98px) {
    .card-body {
        padding: 1rem !important;
    }
    
    .integration-card img {
        width: 32px !important;
        height: 32px !important;
    }
    
    .card-title {
        font-size: 0.9rem;
    }
    
    .card-text {
        font-size: 0.8rem;
    }
    
    .badge {
        font-size: 0.65rem;
    }
}

/* Tablet optimizations */
@media (min-width: 576px) and (max-width: 767.98px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Desktop optimizations */
@media (min-width: 768px) {
    .container-fluid {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

/* Grid improvements for different screen sizes */
@media (min-width: 1200px) {
    .col-lg-4 {
        flex: 0 0 auto;
        width: 33.333333%;
    }
}

@media (min-width: 1400px) {
    .row {
        --bs-gutter-x: 2rem;
    }
}

/* Card content alignment */
.card-body {
    display: flex;
    flex-direction: column;
}

.card-text {
    flex-grow: 1;
    line-height: 1.4;
}

/* Badge positioning improvements */
.badge {
    flex-shrink: 0;
    white-space: nowrap;
}

/* Image styling */
.integration-card img {
    object-fit: contain;
    border-radius: 4px;
}

/* Focus states for accessibility */
.integration-card:focus {
    outline: 2px solid rgba(var(--bs-primary-rgb), 0.5);
    outline-offset: 2px;
}

/* Loading states */
.integration-card.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Text truncation improvements */
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Responsive gap adjustments */
@media (max-width: 575.98px) {
    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }
}
</style>