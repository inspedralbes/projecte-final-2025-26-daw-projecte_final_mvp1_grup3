<template>
  <div class="comment-form">
    <div v-if="replyToName" class="comment-form__reply-tag">
      <span class="comment-form__reply-label">Resposta a <strong>@{{ replyToName }}</strong></span>
      <button type="button" class="comment-form__reply-dismiss" @click="$emit('dismiss-reply')">&times;</button>
    </div>
    <div class="comment-form__row">
      <input
        v-model="content"
        type="text"
        :placeholder="replyToName ? 'Respon a @' + replyToName + '...' : $t('social.write_comment')"
        class="comment-form__input"
        @keyup.enter="submit"
      />
      <button
        @click="submit"
        :disabled="!content.trim() || loading"
        class="comment-form__submit"
      >
        {{ $t('social.send') }}
      </button>
    </div>
    <p v-if="error" class="comment-form__error">{{ error }}</p>
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";

export default {
  name: "CommentForm",
  props: {
    postId: { type: Number, required: true },
    parentId: { type: Number, default: null },
    replyToName: { type: String, default: null }
  },
  emits: ["submitted"],
  data: function () {
    return {
      content: "",
      loading: false,
      error: null
    };
  },
  methods: {
    submit: async function () {
      if (!this.content.trim() || this.loading) return;

      this.loading = true;
      this.error = null;

      var socialStore = useSocialStore();
      var result = await socialStore.addComment(this.postId, this.content, this.parentId);

      if (result) {
        this.content = "";
        this.$emit("submitted");
      } else {
        this.error = socialStore.error || this.$t('social.error_comment');
      }

      this.loading = false;
    }
  }
};
</script>

<style scoped>
.comment-form {
  margin-top: 8px;
  width: 100%;
}

.comment-form__row {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
}

.comment-form__input {
  width: 100%;
  box-sizing: border-box;
  padding: 8px 14px;
  border: 1px solid #e5e5e5;
  border-radius: 10px;
  background: #ffffff;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  color: #2b2d42;
  outline: none;
  transition: border-color 0.2s;
}

.comment-form__input:focus {
  border-color: #79D45D;
  box-shadow: 0 0 0 2px rgba(121, 212, 93, 0.15);
}

.comment-form__submit {
  width: 100%;
  box-sizing: border-box;
  border: 2px solid #6FBC58;
  background: #79D45D;
  color: #ffffff;
  border-radius: 10px;
  padding: 8px 16px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s, opacity 0.15s;
  text-align: center;
}

.comment-form__submit:hover {
  filter: brightness(0.97);
}

.comment-form__submit:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.comment-form__error {
  margin: 4px 0 0;
  color: #ff6b8a;
  font-size: 12px;
}

.comment-form__reply-tag {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 10px;
  margin-bottom: 4px;
  background: rgba(121, 212, 93, 0.1);
  border-radius: 8px;
  border-left: 3px solid #79D45D;
}

.comment-form__reply-label {
  font-size: 12px;
  color: #5b5b5b;
}

.comment-form__reply-label strong {
  color: #79D45D;
}

.comment-form__reply-dismiss {
  border: 0;
  background: transparent;
  color: #b0b0b0;
  font-size: 16px;
  cursor: pointer;
  padding: 0 2px;
  line-height: 1;
}

.comment-form__reply-dismiss:hover {
  color: #5b5b5b;
}
</style>
