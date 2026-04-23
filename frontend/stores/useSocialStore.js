import { defineStore } from "pinia";
import { authFetch } from "~/composables/useApi.js";

export var useSocialStore = defineStore("social", {
  state: function () {
    return {
      posts: [],
      loading: false,
      error: null,
      currentPost: null,
    };
  },
  actions: {
    fetchFeed: async function () {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/posts", {});
        if (!resposta.ok) {
          throw new Error("Error en obtenir posts: " + resposta.status);
        }

        var dadesBrutes = await resposta.json();
        this.posts = dadesBrutes.data || dadesBrutes || [];
        return this.posts;
      } catch (e) {
        this.error = e.message;
        this.posts = [];
        return [];
      } finally {
        this.loading = false;
      }
    },

    createPost: async function (postData) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/posts", {
          method: "POST",
          body: JSON.stringify(postData),
        });
        if (!resposta.ok) {
          throw new Error("Error en crear post: " + resposta.status);
        }

        var nouPost = await resposta.json();
        this.posts.unshift(nouPost.data || nouPost);
        return nouPost;
      } catch (e) {
        this.error = e.message;
        return null;
      } finally {
        this.loading = false;
      }
    },

    getPost: async function (postId) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/posts/" + postId, {});
        if (!resposta.ok) {
          throw new Error("Error en obtenir post: " + resposta.status);
        }

        var post = await resposta.json();
        this.currentPost = post.data || post;
        return this.currentPost;
      } catch (e) {
        this.error = e.message;
        return null;
      } finally {
        this.loading = false;
      }
    },

    deletePost: async function (postId) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/posts/" + postId, {
          method: "DELETE",
        });
        if (!resposta.ok) {
          throw new Error("Error en eliminar post: " + resposta.status);
        }

        this.posts = this.posts.filter(function (p) {
          return p.id !== postId;
        });
        return true;
      } catch (e) {
        this.error = e.message;
        return false;
      } finally {
        this.loading = false;
      }
    },

    addComment: async function (postId, content, parentId) {
      this.loading = true;
      this.error = null;

      try {
        var dades = {
          post_id: postId,
          content: content,
        };
        if (parentId) {
          dades.parent_id = parentId;
        }

        var resposta = await authFetch("/api/social/comments", {
          method: "POST",
          body: JSON.stringify(dades),
        });
        if (!resposta.ok) {
          throw new Error("Error en crear comentari: " + resposta.status);
        }

        var comentari = await resposta.json();
        return comentari.data || comentari;
      } catch (e) {
        this.error = e.message;
        return null;
      } finally {
        this.loading = false;
      }
    },

    getComments: async function (postId) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/comments/" + postId, {});
        if (!resposta.ok) {
          throw new Error("Error en obtenir comentaris: " + resposta.status);
        }

        var comentaris = await resposta.json();
        return comentaris.data || comentaris;
      } catch (e) {
        this.error = e.message;
        return [];
      } finally {
        this.loading = false;
      }
    },

    deleteComment: async function (commentId) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/comments/" + commentId, {
          method: "DELETE",
        });
        if (!resposta.ok) {
          throw new Error("Error en eliminar comentari: " + resposta.status);
        }

        return true;
      } catch (e) {
        this.error = e.message;
        return false;
      } finally {
        this.loading = false;
      }
    },

    toggleLike: async function (likeableId, likeableType) {
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/likes", {
          method: "POST",
          body: JSON.stringify({
            likeable_id: likeableId,
            likeable_type: likeableType,
          }),
        });
        if (!resposta.ok) {
          throw new Error("Error en fer like: " + resposta.status);
        }

        var result = await resposta.json();

        // Actualitzar l'estat en l'store per a l'usuari actual
        var i, j;
        var normalizedCount = Number(result.likes_count);
        if (likeableType === 'post') {
          for (i = 0; i < this.posts.length; i++) {
            if (this.posts[i].id == likeableId) {
              this.posts[i].likes_count = normalizedCount;
              this.posts[i].liked_by_current_user = result.liked;
              // Forçar reactivitat si cal
              this.posts[i] = { ...this.posts[i] };
              break;
            }
          }
        } else if (likeableType === 'comment') {
          for (i = 0; i < this.posts.length; i++) {
            if (this.posts[i].comments) {
              for (j = 0; j < this.posts[i].comments.length; j++) {
                if (this.posts[i].comments[j].id == likeableId) {
                  this.posts[i].comments[j].likes_count = normalizedCount;
                  this.posts[i].comments[j].liked_by_current_user = result.liked;
                  // Forçar reactivitat al post que conté el comentari
                  this.posts[i] = { ...this.posts[i] };
                  break;
                }
              }
            }
          }
        }

        return result;
      } catch (e) {
        this.error = e.message;
        return null;
      }
    },

    checkLiked: async function (likeableId, likeableType) {
      this.error = null;

      try {
        var url = "/api/social/likes/check?likeable_id=" + likeableId + "&likeable_type=" + likeableType;
        var resposta = await authFetch(url, {});
        if (!resposta.ok) {
          throw new Error("Error en verificar like: " + resposta.status);
        }

        var result = await resposta.json();
        return result.liked || false;
      } catch (e) {
        this.error = e.message;
        return false;
      }
    },

    importHabit: async function (postId, diesSetmana) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/import/habit", {
          method: "POST",
          body: JSON.stringify({
            post_id: postId,
            dies_setmana: diesSetmana,
          }),
        });
        if (!resposta.ok) {
          throw new Error("Error en importar hàbit: " + resposta.status);
        }

        var result = await resposta.json();
        return result;
      } catch (e) {
        this.error = e.message;
        return null;
      } finally {
        this.loading = false;
      }
    },

    importPlantilla: async function (postId, habitIds) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/import/plantilla", {
          method: "POST",
          body: JSON.stringify({
            post_id: postId,
            habit_ids: habitIds,
          }),
        });
        if (!resposta.ok) {
          throw new Error("Error en importar plantilla: " + resposta.status);
        }

        var result = await resposta.json();
        return result;
      } catch (e) {
        this.error = e.message;
        return null;
      } finally {
        this.loading = false;
      }
    },

    reportPost: async function (postId, reason) {
      this.error = null;

      try {
        var resposta = await authFetch("/api/social/report", {
          method: "POST",
          body: JSON.stringify({
            content_id: postId,
            content_type: "post",
            reason: reason,
          }),
        });
        if (!resposta.ok) {
          throw new Error("Error en reportar: " + resposta.status);
        }

        return true;
      } catch (e) {
        this.error = e.message;
        return false;
      }
    },

    handleNewPost: function (post) {
      var trobat = false;
      var i;
      for (i = 0; i < this.posts.length; i++) {
        if (this.posts[i].id === post.id) {
          trobat = true;
          break;
        }
      }
      if (!trobat) {
        post.liked_by_current_user = false;
        this.posts.unshift(post);
      }
    },

    handleNewComment: function (comment) {
      var i;
      for (i = 0; i < this.posts.length; i++) {
        if (this.posts[i].id === comment.post_id) {
          if (!this.posts[i].comments) {
            this.posts[i].comments = [];
          }
          // Evitar duplicats
          var jaExisteix = this.posts[i].comments.some(function (c) { return c.id == comment.id; });
          if (!jaExisteix) {
            comment.liked_by_current_user = false;
            this.posts[i].comments.push(comment);
            this.posts[i].comments_count = (Number(this.posts[i].comments_count) || 0) + 1;
            // Forçar reactivitat
            this.posts[i] = { ...this.posts[i] };
          }
          break;
        }
      }
    },

    handleLikeUpdate: function (data) {
      var i, j;
      var normalizedCount = Number(data.likes_count);
      if (data.likeable_type === 'post') {
        for (i = 0; i < this.posts.length; i++) {
          if (this.posts[i].id == data.likeable_id) {
            this.posts[i].likes_count = normalizedCount;
            // Forçar reactivitat
            this.posts[i] = { ...this.posts[i] };
            break;
          }
        }
      } else if (data.likeable_type === 'comment') {
        for (i = 0; i < this.posts.length; i++) {
          if (this.posts[i].comments) {
            for (j = 0; j < this.posts[i].comments.length; j++) {
              if (this.posts[i].comments[j].id == data.likeable_id) {
                this.posts[i].comments[j].likes_count = normalizedCount;
                // Forçar reactivitat al post
                this.posts[i] = { ...this.posts[i] };
                return;
              }
            }
          }
        }
      }
    },
  },
});
