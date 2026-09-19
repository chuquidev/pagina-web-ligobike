<script setup lang="ts">
withDefaults(defineProps<{ color?: string; opacity?: number }>(), { color: 'white', opacity: 0.1 })

const patternId = `bike-pattern-${Math.random().toString(36).slice(2, 9)}`
</script>

<template>
    <svg class="bike-pattern-drift pointer-events-none absolute -inset-10 h-[130%] w-[130%]" :style="{ opacity }"
        aria-hidden="true">
        <defs>
            <pattern :id="patternId" width="140" height="100" patternUnits="userSpaceOnUse"
                patternTransform="rotate(-8)">
                <g :stroke="color" fill="none" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="24" cy="60" r="16" />
                    <circle cx="96" cy="60" r="16" />
                    <circle cx="24" cy="60" r="2" :fill="color" stroke="none" />
                    <circle cx="96" cy="60" r="2" :fill="color" stroke="none" />
                    <path d="M24 60 L52 60 L40 24 L68 24 L96 60" />
                    <path d="M52 60 L68 24" />
                    <circle cx="52" cy="60" r="2.5" :fill="color" stroke="none" />
                    <path d="M40 24 L34 16" />
                    <path d="M68 24 L74 12 L82 12" />
                </g>
            </pattern>
        </defs>
        <rect width="100%" height="100%" :fill="`url(#${patternId})`" />
    </svg>
</template>

<style scoped>
.bike-pattern-drift {
    animation: bike-drift 50s linear infinite;
}

@keyframes bike-drift {
    0% {
        transform: translate(0, 0);
    }

    50% {
        transform: translate(-3%, -2%);
    }

    100% {
        transform: translate(0, 0);
    }
}
</style>