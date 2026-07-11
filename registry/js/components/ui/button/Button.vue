<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Primitive } from 'reka-ui';
import type { PrimitiveProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { useAttrs } from 'vue';
import type { ButtonVariants } from '.';
import { cn } from '@/lib/utils';
import { buttonVariants } from '.';

defineOptions({
    inheritAttrs: false,
});

interface Props extends PrimitiveProps {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    class?: HTMLAttributes['class'];
    href?: InertiaLinkProps['href'];
    confirm?: string;
    disabled?: boolean;
    onBefore?: InertiaLinkProps['onBefore'];
}

const attrs = useAttrs();
const props = withDefaults(defineProps<Props>(), {
    as: 'button',
    href: undefined,
    confirm: undefined,
    disabled: false,
    onBefore: undefined,
});

function confirmAction(): boolean {
    return !props.confirm || window.confirm(props.confirm);
}

function handleClick(event: MouseEvent): void {
    if (props.disabled || !confirmAction()) {
        event.preventDefault();
        event.stopImmediatePropagation();
    }
}

function handleBeforeVisit(
    ...parameters: Parameters<NonNullable<InertiaLinkProps['onBefore']>>
): boolean | void {
    if (props.disabled || !confirmAction()) {
        return false;
    }

    return props.onBefore?.(...parameters);
}
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        :class="cn(buttonVariants({ variant, size }), props.class)"
        :aria-disabled="disabled || undefined"
        :tabindex="disabled ? -1 : undefined"
        v-bind="attrs"
        :on-before="handleBeforeVisit"
        data-slot="button"
    >
        <slot />
    </Link>

    <Primitive
        v-else
        data-slot="button"
        :data-variant="variant"
        :data-size="size"
        :as="as"
        :as-child="asChild"
        v-bind="attrs"
        :disabled="disabled"
        :class="cn(buttonVariants({ variant, size }), props.class)"
        @click.capture="handleClick"
    >
        <slot />
    </Primitive>
</template>
