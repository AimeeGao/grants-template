<template>
    <Head title="Institution Dashboard" />

    <AuthenticatedLayout v-bind="$attrs">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Institution Dashboard
            </h2>
        </template>

        <div class="container-fluid py-12">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-header">
                            Institution Menu
                        </div>
                        <div class="card-body p-0">
                            <DashboardMenu :page="page" />
                        </div>
                    </div>
                </div>

                <div class="col-md-9 mb-3">

                    <InstitutionProfile
                        v-if="page === 'profile'"
                        :institution="institution"
                        :canEdit="canEdit"
                    />

                    <div v-if="page === 'dashboard'" class="card">
                        <div class="card-header">Dashboard Overview</div>
                        <div class="card-body">
                            <h4>Welcome to {{ institutionName }}</h4>
                            <p class="text-muted">This is your institution dashboard. Use the menu to navigate.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Head } from '@inertiajs/vue3';
import DashboardMenu from '../Components/DashboardMenu.vue';
import InstitutionProfile from '../Components/InstitutionProfile.vue';

defineProps({
    page: String,
    institution: Object,
    institutionName: String,
    canEdit: Boolean,
});
</script>
