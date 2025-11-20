<template>
    <nav class="navbar navbar-expand-lg sticky-top navbar-dark">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a class="navbar-brand" href="/institution/dashboard">
                <span class="fw-light">StrongerBC Future Skills Grant</span>
            </a>

            <!-- Right side: User name -->
            <div class="ms-auto">
                <span class="text-white">{{ userName }}</span>
            </div>
        </div>
    </nav>
</template>

<script>
export default {
    name: 'NavBar',
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

.text-white {
    color: white;
}
</style>
