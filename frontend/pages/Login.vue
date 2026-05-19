<!--
  Component o pagina Nuxt: Login.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
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
