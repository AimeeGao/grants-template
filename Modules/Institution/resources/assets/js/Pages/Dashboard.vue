<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout v-bind="$attrs">
        <div class="container-fluid py-4">
            <div class="row">
                <!-- Left Sidebar: Menu -->
                <div class="col-12 col-md-3 col-lg-2">
                    <DashboardMenu
                        :active-page="currentPage"
                        @navigate="navigateTo"
                    />
                </div>

                <!-- Right: Main Content Area -->
                <div class="col-12 col-md-9 col-lg-10">
                    <component
                        :is="currentComponent"
                        v-bind="currentProps"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Head } from '@inertiajs/vue3';
import DashboardMenu from '../Components/DashboardMenu.vue';
import DashboardOverview from '../Components/DashboardOverview.vue';
import InstitutionProfile from '../Components/InstitutionProfile.vue';
import ApplicationList from '../Components/ApplicationList.vue';
import AttestationList from '../Components/AttestationList.vue';
import ReportsList from '../Components/ReportsList.vue';

export default {
    name: 'Dashboard',
    components: {
        AuthenticatedLayout,
        Head,
        DashboardMenu,
        DashboardOverview,
        InstitutionProfile,
        ApplicationList,
        AttestationList,
        ReportsList,
    },
    props: {
        institutionName: {
            type: String,
            default: 'Unknown Institution'
        },
        institution: {
            type: Object,
            default: null
        },
        attestationData: {
            type: Object,
            default: () => ({
                totalAttestations: 0,
                reservedGradAttestations: 0,
                availableAttestations: 0,
                gradIssued: 0,
                gradDeclined: 0,
                undergradIssued: 0,
                undergradDeclined: 0,
                remainingUndergradAttestations: 0,
            })
        },
        canEdit: {
            type: Boolean,
            default: false
        },
        applications: {
            type: Object,
            default: null
        },
        applicationStats: {
            type: Object,
            default: null
        },
        filters: {
            type: Object,
            default: null
        },
        attestations: {
            type: Object,
            default: null
        },
        attestationStats: {
            type: Object,
            default: null
        },
        reportData: {
            type: Object,
            default: null
        }
    },
    setup(props, { attrs }) {
        const currentPage = ref('dashboard');

        const navigateTo = (page) => {
            currentPage.value = page;
        };

        const currentComponent = computed(() => {
            switch (currentPage.value) {
                case 'dashboard':
                    return 'DashboardOverview';
                case 'profile':
                    return 'InstitutionProfile';
                case 'applications':
                    return 'ApplicationList';
                case 'attestations':
                    return 'AttestationList';
                case 'reports':
                    return 'ReportsList';
                default:
                    return 'DashboardOverview';
            }
        });

        const currentProps = computed(() => {
            const userName = attrs.auth?.user
                ? `${attrs.auth.user.first_name} ${attrs.auth.user.last_name}`
                : 'User';

            switch (currentPage.value) {
                case 'dashboard':
                    return {
                        institutionName: props.institutionName,
                        userName: userName,
                        attestationData: props.attestationData,
                    };
                case 'profile':
                    return {
                        institution: props.institution,
                        canEdit: props.canEdit,
                    };
                case 'applications':
                    return {
                        applications: props.applications,
                        applicationStats: props.applicationStats,
                        filters: props.filters,
                        canEdit: props.canEdit,
                    };
                case 'attestations':
                    return {
                        attestations: props.attestations,
                        attestationStats: props.attestationStats,
                        filters: props.filters,
                        canEdit: props.canEdit,
                    };
                case 'reports':
                    return {
                        reportData: props.reportData,
                        filters: props.filters,
                    };
                default:
                    return {};
            }
        });

        return {
            currentPage,
            navigateTo,
            currentComponent,
            currentProps,
        };
    }
}
</script>

<style scoped>
/* Ensure full width for dashboard container */
.container-fluid {
    max-width: 100%;
}
</style>
