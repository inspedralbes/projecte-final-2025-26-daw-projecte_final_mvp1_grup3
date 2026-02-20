<template>
	<div class="min-h-screen bg-gray-50 py-12 px-8 lg:px-20 flex items-center">
		<div class="w-full">
			<div class="grid grid-cols-2 gap-12 items-center">
				<!-- Left: Login card -->
				<div class="bg-white rounded-xl p-8 shadow-md" style="max-width:420px">
					<div class="flex flex-col items-center gap-4">
						<div class="w-20 h-20 rounded-full bg-green-500/10 flex items-center justify-center">
							<div class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center font-semibold">∞</div>
						</div>
						<h2 class="text-2xl font-semibold text-gray-800">Loopy Master</h2>
						<p class="text-sm text-gray-500 text-center">Domina tus hábitos, sube de nivel.</p>
					</div>

					<form class="mt-6 space-y-4">
						<div>
							<label class="block text-xs font-medium text-gray-600 mb-2">EMAIL</label>
							<input type="email" placeholder="alex.martinez@ejemplo.com" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" />
						</div>
						<div>
							<label class="block text-xs font-medium text-gray-600 mb-2">CONTRASEÑA</label>
							<input type="password" placeholder="••••••••" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" />
						</div>

						<div class="pt-2 grid grid-cols-2 gap-3">
							<button type="button" class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg">ADMIN</button>
							<NuxtLink to="/">
								<button type="button" class="w-full bg-green-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg">USER</button>
							</NuxtLink>
						</div>

						<div class="pt-2">
							<NuxtLink to="/registre">
								<button type="button" class="w-full bg-white border border-gray-200 text-gray-700 font-medium py-3 rounded-lg hover:bg-gray-50">REGISTRAR-SE</button>
							</NuxtLink>
						</div>

						<div class="text-center text-xs text-green-600 mt-3">¿Has olvidado tu contraseña?</div>
					</form>
				</div>

				<!-- Right: dashboard preview -->
				<div class="grid grid-rows-3 gap-6 h-full">
					<!-- Top row: small avatar card + progress + spacer -->
					<div class="grid grid-cols-3 gap-6 items-start">
						<div class="col-span-1"></div>
						<div class="col-span-1 bg-white rounded-xl p-4 shadow-sm flex flex-col items-center">
							<div class="text-sm font-medium mt-3">Tu Compañero</div>
							<div class="text-xs text-gray-400">Nivel 5 · Guardián</div>
						</div>
						<div class="col-span-1 bg-white rounded-xl p-3 shadow-sm flex flex-col justify-between">
							<div class="text-xs text-gray-400">Progreso Diario</div>
							<div class="w-full bg-gray-100 rounded-full h-3 mt-3 overflow-hidden">
								<div class="bg-green-500 h-3 rounded-full" style="width:60%"></div>
							</div>
						</div>
					</div>

					<!-- Middle row: big central pet card -->
					<div class="flex items-center justify-center">
						<div class="bg-white rounded-xl p-8 shadow-md w-full max-w-xl flex items-center gap-6">
							<div class="w-24 h-24 rounded-xl bg-amber-50 flex items-center justify-center">
							</div>
							<div>
								<div class="text-lg font-semibold">TUMONSTRUO</div>
								<div class="text-sm text-gray-400">Nivel 5 · Guardián de los Bosques</div>
							</div>
							<div class="ml-auto text-sm text-gray-500">⭐ 42</div>
						</div>
					</div>

					<!-- Bottom row: two colored cards -->
					<div class="grid grid-cols-3 gap-6 items-end">
						<div class="col-span-1 bg-blue-50 rounded-xl p-6 shadow-sm flex items-center justify-center">
							<div class="text-center">
								<div class="text-sm text-gray-500">Beber Agua</div>
								<div class="text-xs text-gray-400">2 / 4 · Salud</div>
							</div>
						</div>
						<div class="col-span-1"></div>
						<div class="col-span-1 bg-yellow-50 rounded-xl p-6 shadow-sm flex items-center justify-center">
							<div class="text-center">
								<div class="text-sm text-gray-700">Diario</div>
								<div class="text-xs text-gray-400">Completado · Creatividad</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * Configuració de la pàgina de Login.
 * Es desactiva el layout per defecte.
 */
definePageMeta({ layout: false });

export default {
  /**
   * Retorna les dades inicials del component.
   */
  data: function() {
    return {
      // De moment no hi ha dades dinàmiques
    };
  },

  methods: {
    // Les funcions es definiran aquí seguint l'estil Agent
  }
}
</script>

<style scoped>
/* Pequeños ajustes para simular las sombras y el espaciado del diseño */
.shadow-inner { box-shadow: 0 8px 30px rgba(16,24,40,0.06); }
</style>

