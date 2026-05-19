<!--
  Component o pagina Nuxt: day.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="min-h-[40vh] flex items-center justify-center text-gray-500 text-sm font-medium px-4">
    {{ $t("home.loading") }}
  </div>
</template>

<script>
/**
 * Ruta legacy /calendar/day?date=… → redirigeix a la mateixa vista que la home amb snapshot (només lectura).
 */
export default {
  name: "CalendarDayRedirectPage",
  mounted: function () {
    var d = this.$route && this.$route.query ? this.$route.query.date : null;
    if (d && typeof d === "string" && d.length >= 8) {
      navigateTo({ path: "/home", query: { date: String(d) } }, { replace: true });
    } else {
      navigateTo("/calendar", { replace: true });
    }
  },
};
</script>
