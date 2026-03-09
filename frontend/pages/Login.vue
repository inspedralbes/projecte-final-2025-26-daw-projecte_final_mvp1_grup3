<template>
  <div></div>
</template>

<script>
export default {
  mounted: function () {
    navigateTo("/auth/login", { replace: true });
  }
};
</script>
