<template>
    <div class="modal d-block" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Give us your feedback ❤️</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <textarea v-model="text" class="form-control" rows="5"
                        placeholder="Feature, request, bug, improvement or other..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button @click="submit" class="btn btn-success w-100">
                        Send feedback 🚀
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    modelValue: Boolean
})
const emit = defineEmits(['update:modelValue', 'submit'])

const text = ref('')

const submit = () => {
    emit('submit', text.value)
    text.value = ''
    emit('update:modelValue', false)
}
</script>
