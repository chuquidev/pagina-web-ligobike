<script setup lang="ts">
// Filas placeholder mientras carga una tabla: evita el salto de layout y se siente
// más rápido que un simple "Cargando...". Anchos variados para que no se vea repetitivo.
withDefaults(defineProps<{ columns: number; rows?: number }>(), { rows: 5 })

const widths = ['w-3/4', 'w-1/2', 'w-2/3', 'w-1/3', 'w-full', 'w-5/6']
</script>

<template>
    <tr v-for="r in rows" :key="r" class="animate-pulse">
        <td v-for="c in columns" :key="c" class="px-4 py-3">
            <div class="h-4 rounded bg-gray-100 dark:bg-gray-800" :class="widths[(r + c) % widths.length]"></div>
        </td>
    </tr>
</template>