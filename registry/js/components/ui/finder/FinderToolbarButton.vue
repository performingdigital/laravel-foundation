<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { ButtonVariants } from '@/components/ui/button';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    class?: HTMLAttributes['class'];
    disabled?: boolean;
}>();
</script>

<template>
    <Button
        type="button"
        :variant="variant"
        :size="size ?? 'sm'"
        :class="props.class"
        :disabled="disabled"
    >
        <slot />
    </Button>
</template>
