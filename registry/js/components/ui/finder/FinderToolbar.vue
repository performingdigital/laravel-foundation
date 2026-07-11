<script setup lang="ts">
defineProps<{
    selectedCount?: number;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-lg border bg-card p-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <div v-if="selectedCount !== undefined" class="text-sm font-medium">
            {{ selectedCount }} selected
        </div>

        <ul class="flex flex-wrap gap-2">
            <slot />
        </ul>
    </div>
</template>
