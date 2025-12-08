<template>
    <div class="dashboard-menu">
        <div class="list-group">
            <Link
                :href="route('institution.dashboard')"
                class="list-group-item list-group-item-action"
                :class="{ active: page === 'dashboard' }"
            >
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </Link>

            <Link
                :href="route('institution.profile.index')"
                class="list-group-item list-group-item-action"
                :class="{ active: page === 'profile' }"
            >
                <i class="bi bi-building me-2"></i>
                Institution Profile
            </Link>

            <!-- TODO: Implement applications feature -->
            <!-- <Link
                :href="route('institution.applications')"
                class="list-group-item list-group-item-action"
                :class="{ active: page === 'applications' }"
            >
                <i class="bi bi-file-earmark-text me-2"></i>
                Applications
            </Link> -->

            <!-- TODO: Implement attestations feature -->
            <!-- <Link
                :href="route('institution.attestations')"
                class="list-group-item list-group-item-action"
                :class="{ active: page === 'attestations' }"
            >
                <i class="bi bi-award me-2"></i>
                Attestations
            </Link> -->

            <!-- TODO: Implement reports feature -->
            <!-- <Link
                :href="route('institution.reports')"
                class="list-group-item list-group-item-action"
                :class="{ active: page === 'reports' }"
            >
                <i class="bi bi-bar-chart me-2"></i>
                Reports
            </Link> -->
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    page: {
        type: String,
        default: 'dashboard'
    }
});
</script>

<style scoped>
.dashboard-menu {
    position: sticky;
    top: 80px;
}

.list-group-item {
    border-color: #dee2e6;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
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