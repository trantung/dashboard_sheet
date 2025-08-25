<template>
    <div class="container-fluid px-2 px-md-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2">
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fs-5 fs-md-4">My Files</h4>
                <span class="badge bg-primary">BETA</span>
            </div>
            <button class="btn btn-success w-100 w-md-auto" @click="openTrialDialog">
                <i class="bi bi-upload me-1"></i>
                Upload Image
            </button>
        </div>

        <div class="bg-white rounded p-3 p-md-5 text-center">
            <i class="bi bi-images fs-1 text-muted mb-3"></i>
            <p class="text-muted mb-0">No images uploaded yet.</p>
        </div>
    </div>

    <TrialLimitDialog
        :show="showDialog"
        :showAlert="false"
        :closable="false"
        alertMessage=""
        @close="closeTrialDialog"
        @change-tab="handleUpgrade"
    />
</template>

<script>
import TrialLimitDialog from '../TrialLimitDialog.vue';

export default {
    name: 'MyFiles',
    components: {
        TrialLimitDialog
    },
    data() {
        return {
            showDialog: false
        }
    },
    methods: {
        openTrialDialog() {
            this.showDialog = true;
        },
        closeTrialDialog() {
            this.showDialog = false;
        },
        handleUpgrade() {
            this.$emit('change-tab', 'pricing');
            this.closeTrialDialog();
        }
    }
}
</script>


<style scoped>
@media (min-width: 768px) {
    .w-md-auto {
        width: auto !important;
    }
}
</style>
