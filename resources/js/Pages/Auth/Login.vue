<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-sm" style="min-width: 360px;">
      <div class="card-body text-center">
        <h1 class="h4 mb-3">Welcome back</h1>
        
        <div v-if="$page.props.errors && $page.props.errors.error" class="alert alert-danger mb-4">
          {{ $page.props.errors.error }}
        </div>

        <div v-if="status" class="alert alert-success mb-4">
          {{ status }}
        </div>
        <div v-if="$page.props.flash && $page.props.flash.success" class="alert alert-success mb-4">
          {{ $page.props.flash.success }}
        </div>

        <p class="text-muted mb-4">
          Sign in with one of the identity providers to continue.
        </p>
        <div class="d-grid gap-2">
          <a class="btn btn-primary" href="/portal-login">Continue with IDIR</a>
          <a class="btn btn-outline-primary" href="/bcsc-login">BC Services Card</a>
          <a class="btn btn-outline-secondary" href="/bceid-login">BCeID</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
    status: String,
});
</script>
