<template>
	<div class="min-h-screen bg-gray-50 py-12 px-8 lg:px-20 flex items-center">
		<div class="w-full">
			<div class="grid grid-cols-2 gap-12 items-stretch px-8 py-8 bg-white rounded-2xl shadow-sm">
				<!-- Left: Register card -->
				<div class="bg-white rounded-xl p-8 shadow-md" style="max-width:420px">
					<div class="flex flex-col items-center gap-4">
						<div class="w-20 h-20 rounded-full bg-green-500/10 flex items-center justify-center">
							<div class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center font-semibold">∞</div>
						</div>
						<h2 class="text-2xl font-semibold text-gray-800">Únete a Loopy</h2>
						<p class="text-sm text-gray-500 text-center">Crea tu cuenta y empieza tu viaje.</p>
					</div>

					<form class="mt-6 space-y-4">
						<div>
							<label class="block text-xs font-medium text-gray-600 mb-2">NOMBRE</label>
							<input type="text" placeholder="Tu nombre" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" />
						</div>
						<div>
							<label class="block text-xs font-medium text-gray-600 mb-2">EMAIL</label>
							<input type="email" placeholder="tu.email@ejemplo.com" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" />
						</div>
						<div>
							<label class="block text-xs font-medium text-gray-600 mb-2">CONTRASEÑA</label>
							<input type="password" placeholder="••••••••" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" />
						</div>
						<div>
							<label class="block text-xs font-medium text-gray-600 mb-2">CONFIRMAR CONTRASEÑA</label>
							<input type="password" placeholder="••••••••" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" />
						</div>

						<div class="pt-2">
							<button type="button" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg">REGISTRARSE</button>
						</div>

						<div class="pt-2">
							<NuxtLink to="/login">
								<button type="button" class="w-full bg-white border border-gray-200 text-gray-700 font-medium py-3 rounded-lg hover:bg-gray-50">VOLVER AL LOGIN</button>
							</NuxtLink>
						</div>
					</form>
				</div>

				<!-- Right: Test Quiz -->
				<div class="grid grid-cols-2 gap-4 h-full">
					<!-- Paso 1: Pregunta Maestra -->
					<template v-if="selectedCategory === null">
						<div class="col-span-2 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 shadow-sm flex flex-col items-center justify-center">
							<div class="text-xs font-bold text-gray-600 text-center">¿En qué área te vas a centrar?</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-blue-50" @click="selectCategory('gym')">
							<div class="text-xs font-medium text-gray-700">💪 Activitat física</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-green-50" @click="selectCategory('nutrition')">
							<div class="text-xs font-medium text-gray-700">🥗 Alimentació</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-yellow-50" @click="selectCategory('study')">
							<div class="text-xs font-medium text-gray-700">📚 Estudi</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-purple-50" @click="selectCategory('reading')">
							<div class="text-xs font-medium text-gray-700">📖 Lectura</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-pink-50" @click="selectCategory('wellness')">
							<div class="text-xs font-medium text-gray-700">🧘 Benestar</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-red-50" @click="selectCategory('smoking')">
							<div class="text-xs font-medium text-gray-700">🚭 Vida sense Fum</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-indigo-50" @click="selectCategory('cleaning')">
							<div class="text-xs font-medium text-gray-700">🏠 Neteja Express</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-gray-50" @click="selectCategory('hobby')">
							<div class="text-xs font-medium text-gray-700">🎨 Modelisme</div>
						</div>
					</template>

					<!-- Paso 2: Preguntas de Profundización -->
					<template v-else>
						<div v-for="(pregunta, index) in getCurrentQuestions()" :key="pregunta.id" class="bg-white rounded-xl p-3 shadow-sm flex flex-col items-center justify-center overflow-hidden">
							<div class="text-xs font-bold text-gray-600 text-center mb-2">Pregunta {{ index + 1 }}/{{ getCurrentQuestions().length }}</div>
							<div class="text-xs text-gray-700 text-center leading-tight mb-3">{{ pregunta.pregunta }}</div>
							<div class="flex gap-2">
								<!-- Botones de respuesta condicionales -->
								<template v-if="pregunta.pregunta.includes('força o massa muscular')">
									<button @click="respondre(pregunta.id, 'forza')" :class="['px-3 py-1 text-xs rounded-md', respostes[pregunta.id] === 'forza' ? 'bg-blue-500 text-white' : 'bg-gray-200']">Força</button>
									<button @click="respondre(pregunta.id, 'massa_muscular')" :class="['px-3 py-1 text-xs rounded-md', respostes[pregunta.id] === 'massa_muscular' ? 'bg-blue-500 text-white' : 'bg-gray-200']">Massa Muscular</button>
								</template>
								<template v-else-if="pregunta.pregunta.includes('ansietat o per compromís social')">
									<button @click="respondre(pregunta.id, 'ansietat')" :class="['px-3 py-1 text-xs rounded-md', respostes[pregunta.id] === 'ansietat' ? 'bg-blue-500 text-white' : 'bg-gray-200']">Per Ansietat</button>
									<button @click="respondre(pregunta.id, 'compromis')" :class="['px-3 py-1 text-xs rounded-md', respostes[pregunta.id] === 'compromis' ? 'bg-blue-500 text-white' : 'bg-gray-200']">Per Compromís</button>
								</template>
								<template v-else>
									<button @click="respondre(pregunta.id, 'si')" :class="['px-3 py-1 text-xs rounded-md', respostes[pregunta.id] === 'si' ? 'bg-blue-500 text-white' : 'bg-gray-200']">Sí</button>
									<button @click="respondre(pregunta.id, 'no')" :class="['px-3 py-1 text-xs rounded-md', respostes[pregunta.id] === 'no' ? 'bg-blue-500 text-white' : 'bg-gray-200']">No</button>
								</template>
							</div>
						</div>
						<div class="col-span-full mt-2 grid grid-cols-2 gap-2">
							<button @click="selectCategory(null)" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 rounded-lg text-xs">TORNAR</button>
							<button @click="finalitzarTest" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg text-xs">FINALITZAR</button>
						</div>
					</template>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'

definePageMeta({ layout: false })

const selectedCategory = ref(null)
const questions = ref([])
const respostes = ref({})

const categoryMap = {
    'gym': 1,
    'nutrition': 2,
    'study': 3,
    'reading': 4,
    'wellness': 5,
    'smoking': 6,
    'cleaning': 7,
    'hobby': 8
}

const selectCategory = async (category) => {
    selectedCategory.value = category
    if (category) {
        const categoryId = categoryMap[category]
        const { data } = await useFetch(`http://localhost:8000/api/preguntes-registre/${categoryId}`)
        if (data.value && data.value.preguntes) {
            questions.value = data.value.preguntes
        }
    } else {
		// Netejar respostes en tornar
        questions.value = []
		respostes.value = {}
    }
}

const getCurrentQuestions = () => {
	return questions.value
}

const respondre = (preguntaId, resposta) => {
	respostes.value[preguntaId] = resposta
}

const finalitzarTest = () => {
	// Aquí es faria un INSERT a la base de dades amb les respostes.
	// De moment, només les mostrem a la consola.
	console.log('Respostes a enviar:', JSON.stringify(respostes.value))
	alert('Test finalitzat! Revisa la consola per veure les teves respostes.')
	
	// Tornem a la selecció de categoria
	selectedCategory.value = null
	questions.value = []
	respostes.value = {}
}
</script>

<style scoped>
/* Pequeños ajustes para simular las sombras y el espaciado del diseño */
.shadow-inner { box-shadow: 0 8px 30px rgba(16,24,40,0.06); }

/* Estils per als botons de resposta seleccionats */
.bg-blue-500 {
	background-color: #3b82f6;
}
.text-white {
	color: #ffffff;
}
.bg-gray-200 {
	background-color: #e5e7eb;
}
</style>
