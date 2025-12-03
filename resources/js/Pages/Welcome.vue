<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="text-center">
      <h1 class="display-4 fw-bold mb-3">Welcome to {{ appName }}</h1>
      <p class="lead text-muted mb-4">Your Laravel + Inertia starter page.</p>
      <a class="btn btn-primary px-4" href="/login">Go to login</a>
    </div>
  </div>
</template>
 
<script setup>
  const appName = 'Grants Template';
</script>