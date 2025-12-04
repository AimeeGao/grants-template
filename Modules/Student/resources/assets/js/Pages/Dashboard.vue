<template>
    <Head title="Student Dashboard" />

    <AuthenticatedLayout v-bind="$attrs">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Student Dashboard
            </h2>
        </template>

        <div class="container-fluid py-12">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-header">
                            Dashboard Menu
                        </div>
                        <div class="card-body p-0">
                            <DashboardMenu :page="page" />
                        </div>
                    </div>
                </div>

                <div class="col-md-9 mb-3">
                    
                    <DashboardProfile 
                        v-if="page === 'profile'" 
                        :profileData="profileData"
                        :demographics="demographics"
                        :existingDemographics="existingDemographics"
                    />

                    <div v-if="page === 'dashboard'" class="card">
                        <div class="card-header">My Applications</div>
                        <div class="card-body">
                            <p class="text-muted">You have not submitted any applications yet.</p>
                            <button class="btn btn-primary">Start New Application</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/vue3';
import DashboardMenu from '../Components/DashboardMenu.vue';
import DashboardProfile from '../Components/DashboardProfile.vue';


defineProps({
    page: String,
    profileData: Object,
    demographics: Array,
    existingDemographics: [Array, Object],
});
</script>
