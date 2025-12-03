<template>
    <div class="list-group">
        <Link 
            :href="route('student.dashboard')" 
            class="list-group-item list-group-item-action" 
            :class="{ 'active': page === 'dashboard' }"
        >
            Dashboard
        </Link>

        <Link 
            :href="route('student.profile.index')" 
            class="list-group-item list-group-item-action" 
            :class="{ 'active': page === 'profile' }"
        >
            Profile Details
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';


defineProps({
    page: String
});
</script>

<style scoped>

.list-group-item.active {
    background-color: #003366;
    border-color: #003366;
    color: #fff;
}
</style>