<!--
  Component o pagina Nuxt: TemplatePublicSwitch.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="template-public-switch">
    <input
      :id="inputId"
      type="checkbox"
      class="template-public-switch__native peer sr-only"
      :checked="modelValue"
      @change="onChange"
    />
    <label
      :for="inputId"
      class="template-public-switch__hitbox peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[#6FBC58] peer-focus-visible:rounded-[30px]"
    >
      <!-- Rail únic: fons + vora segons estat; thumb dins -->
      <span
        class="template-public-switch__track"
        :class="modelValue ? 'template-public-switch__track--on' : 'template-public-switch__track--off'"
      >
        <span class="template-public-switch__thumb" aria-hidden="true"></span>
      </span>
    </label>
  </div>
</template>

<script setup>
import { computed } from "vue";

var props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  inputId: {
    type: String,
    required: true,
  },
});

var emit = defineEmits(["update:modelValue"]);

function onChange(e) {
  emit("update:modelValue", e.target.checked);
}
</script>

<style scoped>
.template-public-switch {
  width: 100%;
}

.template-public-switch__hitbox {
  display: block;
  width: 100%;
  max-width: 338px;
  margin-inline: auto;
  cursor: pointer;
  user-select: none;
}

/* Rail Figma: 338×58, rx 28.5 → vora 2px (#6FBC58 / #535353) */
.template-public-switch__track {
  --thumb-w: min(154px, calc(100% - 124px));

  position: relative;
  display: block;
  width: 100%;
  height: 58px;
  box-sizing: border-box;
  border-radius: 28.5px;
  border-width: 2px;
  border-style: solid;
  overflow: hidden;
  transition:
    background-color 0.28s cubic-bezier(0.4, 0, 0.2, 1),
    border-color 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}

.template-public-switch__track--off {
  background-color: #e1e0e0;
  border-color: #535353;
}

.template-public-switch__track--on {
  background-color: #79d45d;
  border-color: #6fbc58;
}

/* Thumb Figma: 154×41, rx 20.5, fill #FAF9F9, drop shadow dy 4 blur 2 α 0.25 */
.template-public-switch__thumb {
  position: absolute;
  z-index: 2;
  top: 50%;
  left: 4px;
  width: var(--thumb-w);
  height: 41px;
  border-radius: 20.5px;
  background-color: #faf9f9;
  box-shadow:
    0 4px 4px rgba(0, 0, 0, 0.25),
    0 2px 2px rgba(0, 0, 0, 0.12);
  transform: translateY(-50%);
  transition:
    left 0.32s cubic-bezier(0.4, 0, 0.2, 1),
    transform 0.32s cubic-bezier(0.4, 0, 0.2, 1),
    box-shadow 0.2s ease;
}

.template-public-switch__track--on .template-public-switch__thumb {
  left: calc(100% - 4px);
  transform: translate(-100%, -50%);
}
</style>
