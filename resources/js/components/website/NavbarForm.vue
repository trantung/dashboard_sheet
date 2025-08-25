<template>
    <div class="modal fade" :class="{ show: show }" :style="{ display: show ? 'block' : 'none' }" tabindex="-1"
        @click.self="closeForm">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-2">
                    <div class="w-100 text-center">
                        <div class="bg-primary/10 bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-menu-button-wide text-success fs-4"></i>
                        </div>
                        <h5 class="modal-title">{{ isEdit ? 'Edit navbar item' : 'Create navbar item' }}</h5>
                    </div>
                    <button type="button" class="btn-close position-absolute" style="top: 15px; right: 15px;"
                        @click="closeForm"></button>
                </div>
                <div class="modal-body px-4">
                    <form @submit.prevent="submitForm">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg" placeholder="Title"
                                v-model="form.title">
                        </div>

                        <div class="mb-3">
                            <input type="url" class="form-control form-control-lg" placeholder="https://domain.com"
                                v-model="form.link">
                        </div>

                        <div class="mb-3">
                            <select class="form-select form-select-lg" v-model="form.position" required>
                                <option value="1">Header</option>
                                <option value="2">Footer</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <select class="form-select form-select-lg" v-model="form.target" required>
                                <option value="1">Same tab</option>
                                <option value="2">New tab</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary flex-fill" @click="closeForm">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success flex-fill" :disabled="loading">
                                {{ loading ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div v-if="show" class="modal-backdrop fade show"></div>
</template>

<script setup>
import { ref, watch, computed, defineProps, defineEmits } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    navbarItem: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'submit']);

const loading = ref(false);
const defaultForm = {
    title: '',
    url: '',
    position: 1,
    target: 1
};
const form = ref({ ...defaultForm });

const isEdit = computed(() => props.navbarItem !== null);

const resetForm = () => {
    form.value = { ...defaultForm };
};

const closeForm = () => {
    emit('close');
};

const submitForm = async () => {
    loading.value = true;
    try {
        emit('submit', { ...form.value });
    } finally {
        loading.value = false;
    }
};

watch(() => props.navbarItem, (newVal) => {
    if (newVal) {
        form.value = { ...newVal };
    } else {
        resetForm();
    }
}, { immediate: true });

watch(() => props.show, (newVal) => {
    if (!newVal) {
        resetForm();
    }
});
</script>

<style scoped>
.bg-primary\/10 {
    background-color: rgba(15, 157, 96, .1);
}
</style>