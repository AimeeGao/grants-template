<template>
    <nav class="navbar navbar-expand-lg sticky-top navbar-dark">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a class="navbar-brand" href="/institution/dashboard">
                <span class="fw-light">StrongerBC Future Skills Grant</span>
            </a>

            <!-- Navigation Links -->
            <div class="navbar-nav mx-auto">
                <Link
                    :href="route('institution.dashboard')"
                    class="nav-link"
                    :class="{ active: $page.url === '/institution/dashboard' }"
                >
                    Dashboard
                </Link>
                <Link
                    :href="route('institution.profile')"
                    class="nav-link"
                    :class="{ active: $page.url === '/institution/profile' }"
                >
                    Profile
                </Link>
            </div>

            <!-- Right side: User name -->
            <div class="ms-auto">
                <span class="text-white">{{ userName }}</span>
            </div>
        </div>
    </nav>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    name: 'NavBar',
    components: {
        Link,
    },
    computed: {
        userName() {
            if (this.$attrs.auth && this.$attrs.auth.user) {
                return this.$attrs.auth.user.first_name || 'User';
            }
            return 'User';
        }
    }
}
</script>

<style scoped>
nav.navbar {
    background-color: #003366;
    border-bottom: 2px solid #fcba19;
}

.navbar-brand {
    color: white !important;
    font-size: 1.2rem;
}

.navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.85);
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: #fcba19;
    background-color: rgba(252, 186, 25, 0.1);
}

.navbar-nav .nav-link.active {
    color: #fcba19;
    font-weight: 600;
    background-color: rgba(252, 186, 25, 0.15);
}

.text-white {
    color: white;
}
</style>
