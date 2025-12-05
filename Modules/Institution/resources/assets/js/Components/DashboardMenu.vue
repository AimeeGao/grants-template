<template>
    <div class="dashboard-menu">
        <h5 class="menu-title mb-3">Institution Menu</h5>
        <div class="list-group">
            <button
                type="button"
                class="list-group-item list-group-item-action"
                :class="{ active: activePage === 'dashboard' }"
                @click="$emit('navigate', 'dashboard')"
            >
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </button>

            <button
                type="button"
                class="list-group-item list-group-item-action"
                :class="{ active: activePage === 'profile' }"
                @click="$emit('navigate', 'profile')"
            >
                <i class="bi bi-building me-2"></i>
                Institution Profile
            </button>

            <button
                type="button"
                class="list-group-item list-group-item-action"
                :class="{ active: activePage === 'applications' }"
                @click="$emit('navigate', 'applications')"
            >
                <i class="bi bi-file-earmark-text me-2"></i>
                Applications
            </button>

            <button
                type="button"
                class="list-group-item list-group-item-action"
                :class="{ active: activePage === 'attestations' }"
                @click="$emit('navigate', 'attestations')"
            >
                <i class="bi bi-award me-2"></i>
                Attestations
            </button>

            <button
                type="button"
                class="list-group-item list-group-item-action"
                :class="{ active: activePage === 'reports' }"
                @click="$emit('navigate', 'reports')"
            >
                <i class="bi bi-bar-chart me-2"></i>
                Reports
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    activePage: {
        type: String,
        default: 'dashboard'
    }
});

defineEmits(['navigate']);
</script>

<style scoped>
.dashboard-menu {
    position: sticky;
    top: 80px;
}

.menu-title {
    color: #003366;
    font-weight: 600;
    padding-left: 0.5rem;
}

.list-group-item {
    border-color: #dee2e6;
    transition: all 0.2s ease;
    cursor: pointer;
}

.list-group-item:not(:disabled):hover {
    background-color: #f8f9fa;
    border-color: #003366;
}

.list-group-item.active {
    background-color: #003366;
    border-color: #003366;
    color: #fff;
    font-weight: 600;
}

.list-group-item:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.list-group-item i {
    width: 20px;
    text-align: center;
}

.badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
}
</style>