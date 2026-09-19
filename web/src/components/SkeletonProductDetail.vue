<template>
    <div class="grid gap-8 md:grid-cols-2 md:gap-10">
        <div>
            <div class="aspect-square animate-pulse rounded-2xl bg-gray-200 dark:bg-gray-800"></div>
            <div class="mt-3 flex gap-2">
                <div v-for="n in 3" :key="n"
                    class="h-14 w-14 shrink-0 animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800 sm:h-16 sm:w-16">
                </div>
            </div>
        </div>
        <div>
            <div class="h-3 w-24 animate-pulse rounded bg-gray-200 dark:bg-gray-800"></div>
            <div class="mt-2 h-8 w-3/4 animate-pulse rounded bg-gray-200 dark:bg-gray-800"></div>
            <div class="mt-4 h-9 w-40 animate-pulse rounded bg-gray-200 dark:bg-gray-800"></div>
            <div class="mt-4 h-6 w-28 animate-pulse rounded-full bg-gray-200 dark:bg-gray-800"></div>
            <div class="mt-6 space-y-2">
                <div class="h-4 w-full animate-pulse rounded bg-gray-200 dark:bg-gray-800"></div>
                <div class="h-4 w-5/6 animate-pulse rounded bg-gray-200 dark:bg-gray-800"></div>
                <div class="h-4 w-2/3 animate-pulse rounded bg-gray-200 dark:bg-gray-800"></div>
            </div>
            <div class="mt-8 flex gap-3">
                <div class="h-12 w-44 animate-pulse rounded-full bg-gray-200 dark:bg-gray-800"></div>
                <div class="h-12 w-32 animate-pulse rounded-full bg-gray-200 dark:bg-gray-800"></div>
            </div>
        </div>
    </div>
</template>