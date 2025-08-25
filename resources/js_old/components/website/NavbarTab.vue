<template>
    <div>
        <NotificationAlert ref="notificationAlert" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Navbar</h4>
                <p class="text-muted mb-0">Add navigation menus</p>
            </div>
            <button class="btn btn-success d-flex align-items-center" @click="showCreateForm">
                <i class="bi bi-plus me-2"></i>
                Add navbar item
            </button>
        </div>

        <div v-if="navbarItems.length === 0" class="card border-1">
            <div class="card-body text-center py-5">
                <i class="bi bi-menu-button-wide fs-1 text-muted mb-3"></i>
                <h5 class="mb-2">You don't have any nav items</h5>
                <p class="text-muted mb-3">Add a navigation menu item and choose the display position.</p>
                <button @click="showCreateForm" class="btn btn-success">
                    <i class="bi bi-plus me-1"></i>
                    Add navbar item
                </button>
            </div>
        </div>

        <div v-else class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            <tr v-for="(item, index) in navbarItems" :key="item.id" class="navbar-item-row"
                                :draggable="true" @dragstart="dragStart(index)" @dragover.prevent @drop="drop(index)">
                                <td class="ps-4 py-3" style="width: 40px;">
                                    <i class="bi bi-grip-vertical text-muted drag-handle"></i>
                                </td>
                                <td class="py-3">
                                    <span class="fw-medium">{{ item.title }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex gap-2">
                                        <span class="badge" :class="{
                                            'bg-success': item.position === 1,
                                            'bg-primary': item.position === 2
                                        }">
                                            {{ item.position === 1 ? 'Header' : 'Footer' }}
                                        </span>
                                        <span v-if="item.target === 'new'" class="badge bg-info">
                                            New tab
                                        </span>
                                    </div>
                                </td>
                                <td class="pe-4 py-3 text-end" style="width: 100px;">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-sm btn-outline-primary" @click="editNavbarItem(item)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            @click="deleteNavbarItem(item.id)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <NavbarForm :show="showForm" :navbar-item="editingItem" @close="closeForm" @submit="handleFormSubmit" />

        <ConfirmDialog :show="showDeleteDialog" title="Delete navbar item"
            message="Are you sure you want to delete this navbar item? This action cannot be undone."
            @confirm="confirmDelete" @cancel="cancelDelete" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router'
import axios from 'axios';
import { route as ziggyRoute } from 'ziggy-js';
import NavbarForm from './NavbarForm.vue';
import ConfirmDialog from '../ConfirmDialog.vue';
import NotificationAlert from '@/components/NotificationAlert.vue'

const notificationAlert = ref(null)
const route = useRoute()

const navbarItems = ref([]);
const showForm = ref(false);
const editingItem = ref(null);
const showDeleteDialog = ref(false);
const deletingItemId = ref(null);
const draggedIndex = ref(null);

const websiteId = ref(route.params.id) // Biến để lưu trữ websiteId

const loadNavbarItems = async () => {
    try {
        const response = await axios.get(ziggyRoute('api.navbar.index', { site: websiteId.value }));
        navbarItems.value = response.data;
        notificationAlert.value?.showSuccess('Navbar loaded successfully')
    } catch (error) {
        console.error('Error loading navbar items:', error);
        notificationAlert.value?.showError('Failed to load navbar', 'Loading Error')
        navbarItems.value = [];
    }
};

const showCreateForm = () => {
    editingItem.value = null;
    showForm.value = true;
};

const editNavbarItem = (item) => {
    editingItem.value = { ...item };
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingItem.value = null;
};

const handleFormSubmit = async (formData) => {
    try {
        if (editingItem.value) {
            await axios.put(
                ziggyRoute('api.navbar.update', {
                    site: websiteId.value,
                    id: editingItem.value.id
                }),
                formData
            );
        } else {
            await axios.post(ziggyRoute('api.navbar.store', { site: websiteId.value }), formData);
        }

        await loadNavbarItems();
        closeForm();
    } catch (error) {
        console.error('Error saving navbar item:', error);
    }
};

const deleteNavbarItem = (itemId) => {
    deletingItemId.value = itemId;
    showDeleteDialog.value = true;
};

const confirmDelete = async () => {
    try {
        // await axios.delete(ziggyRoute('api.navbar.destroy', deletingItemId.value));
        await axios.delete(
            ziggyRoute('api.navbar.destroy', {
                site: websiteId.value,
                id: deletingItemId.value
            })
        );
        await loadNavbarItems();
    } catch (error) {
        console.error('Error deleting navbar item:', error);
    }
    cancelDelete();
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    deletingItemId.value = null;
};

const dragStart = (index) => {
    draggedIndex.value = index;
};

const drop = (dropIndex) => {
    if (draggedIndex.value !== null && draggedIndex.value !== dropIndex) {
        const draggedItem = navbarItems.value[draggedIndex.value];
        navbarItems.value.splice(draggedIndex.value, 1);
        navbarItems.value.splice(dropIndex, 0, draggedItem);
        updateItemsOrder();
    }
    draggedIndex.value = null;
};

const updateItemsOrder = async () => {
    try {
        const orderedItems = navbarItems.value.map((item, index) => ({
            id: item.id,
            order: index + 1
        }));
        await axios.post(ziggyRoute('api.navbar.reorder'), { items: orderedItems });
    } catch (error) {
        console.error('Error updating items order:', error);
    }
};

onMounted(() => {
    loadNavbarItems();
});
</script>

<style scoped>
.navbar-item-row {
    cursor: move;
}

.drag-handle {
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

.navbar-item-row:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }

    .btn.btn-success {
        align-self: stretch;
    }

    .table-responsive {
        font-size: 0.9rem;
    }

    .badge {
        font-size: 0.7rem;
    }
}
</style>