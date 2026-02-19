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
				<div class="grid grid-rows-5 gap-4 h-full">
					<!-- Paso 1: Pregunta Maestra -->
					<template v-if="selectedCategory === null">
						<div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 shadow-sm flex flex-col items-center justify-center">
							<div class="text-xs font-bold text-gray-600 text-center">¿En qué área te vas a centrar?</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-blue-50" @click="selectCategory('gym')">
							<div class="text-xs font-medium text-gray-700">💪 Actividad Física</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-green-50" @click="selectCategory('nutrition')">
							<div class="text-xs font-medium text-gray-700">🥗 Alimentación</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-yellow-50" @click="selectCategory('study')">
							<div class="text-xs font-medium text-gray-700">📚 Estudio</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-purple-50" @click="selectCategory('reading')">
							<div class="text-xs font-medium text-gray-700">📖 Lectura</div>
						</div>
						<div class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-pink-50" @click="selectCategory('wellness')">
							<div class="text-xs font-medium text-gray-700">🧘 Bienestar</div>
						</div>
					</template>

					<!-- Paso 2: Preguntas de Profundización -->
					<template v-else>
						<div class="bg-white rounded-xl p-3 shadow-sm flex flex-col items-center justify-center overflow-hidden">
							<div class="text-xs font-bold text-gray-600 text-center mb-2">Pregunta 1/5</div>
							<div class="text-xs text-gray-700 text-center leading-tight">{{ getCurrentQuestions()[0] }}</div>
						</div>
						<div class="bg-white rounded-xl p-3 shadow-sm flex flex-col items-center justify-center overflow-hidden">
							<div class="text-xs font-bold text-gray-600 text-center mb-2">Pregunta 2/5</div>
							<div class="text-xs text-gray-700 text-center leading-tight">{{ getCurrentQuestions()[1] }}</div>
						</div>
						<div class="bg-white rounded-xl p-3 shadow-sm flex flex-col items-center justify-center overflow-hidden">
							<div class="text-xs font-bold text-gray-600 text-center mb-2">Pregunta 3/5</div>
							<div class="text-xs text-gray-700 text-center leading-tight">{{ getCurrentQuestions()[2] }}</div>
						</div>
						<div class="bg-white rounded-xl p-3 shadow-sm flex flex-col items-center justify-center overflow-hidden">
							<div class="text-xs font-bold text-gray-600 text-center mb-2">Pregunta 4/5</div>
							<div class="text-xs text-gray-700 text-center leading-tight">{{ getCurrentQuestions()[3] }}</div>
						</div>
						<div class="bg-white rounded-xl p-3 shadow-sm flex flex-col items-center justify-center overflow-hidden">
							<div class="text-xs font-bold text-gray-600 text-center mb-2">Pregunta 5/5</div>
							<div class="text-xs text-gray-700 text-center leading-tight">{{ getCurrentQuestions()[4] }}</div>
						</div>
						<div class="col-span-full mt-2">
							<button @click="selectCategory(null)" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 rounded-lg text-xs">VOLVER</button>
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

const categories = {
	gym: {
		name: 'Gym Pro',
		questions: [
			'¿Entrenas actualmente en un gimnasio de forma regular?',
			'¿Tu objetivo principal es ganar fuerza o masa muscular?',
			'¿Tienes experiencia previa con el levantamiento de pesas?',
			'¿Dispones de al menos 45 minutos tres veces por semana para entrenar?',
			'¿Te gustaría recibir rutinas específicas de ejercicios compuestos?'
		]
	},
	nutrition: {
		name: 'Dieta Mediterránea',
		questions: [
			'¿Cocinas habitualmente tus propias comidas en casa?',
			'¿Consumes frutas y verduras en casi todas tus comidas diarias?',
			'¿Sueles utilizar aceite de oliva como grasa principal para cocinar?',
			'¿Evitas habitualmente el consumo de bebidas azucaradas y refrescos?',
			'¿Te gustaría planificar tus menús semanales para evitar comer cualquier cosa?'
		]
	},
	study: {
		name: 'Concentración Máxima',
		questions: [
			'¿Sueles estudiar o trabajar en un espacio libre de distracciones?',
			'¿Utilizas alguna técnica de gestión del tiempo (como el método Pomodoro)?',
			'¿Te cuesta arrancar cuando tienes una tarea compleja o larga por delante?',
			'¿Utilizas un calendario o agenda para organizar tus exámenes o entregas?',
			'¿Sientes que aprovechas bien tus horas de mayor energía durante el día?'
		]
	},
	reading: {
		name: 'Club de Lectura',
		questions: [
			'¿Lees habitualmente antes de dormir o mientras vas en transporte público?',
			'¿Tienes una lista de libros pendientes que te gustaría empezar pronto?',
			'¿Te marcas objetivos de páginas o capítulos diarios para avanzar?',
			'¿Sueles dejar los libros a medias por falta de constancia o tiempo?',
			'¿Te gusta anotar o subrayar las ideas que más te inspiran mientras lees?'
		]
	},
	wellness: {
		name: 'Mindfulness',
		questions: [
			'¿Dedicas al menos 5 minutos al día a respirar de forma consciente?',
			'¿Sientes que puedes desconectar totalmente del trabajo al llegar a casa?',
			'¿Practicas algún tipo de estiramiento o yoga de manera habitual?',
			'¿Sueles identificar y analizar tus emociones cuando estás bajo estrés?',
			'¿Priorizas tener un horario de sueño regular para descansar bien?'
		]
	}
}

const selectCategory = (category) => {
	selectedCategory.value = category
}

const getCurrentQuestions = () => {
	if (!selectedCategory.value) return []
	return categories[selectedCategory.value]?.questions || []
}
</script>

<style scoped>
/* Pequeños ajustes para simular las sombras y el espaciado del diseño */
.shadow-inner { box-shadow: 0 8px 30px rgba(16,24,40,0.06); }
</style>
