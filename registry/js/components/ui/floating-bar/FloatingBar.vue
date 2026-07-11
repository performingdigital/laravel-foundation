<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed, onMounted, onUnmounted, provide, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { floatingBarContextKey } from '.';

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        hiddenPaths?: string[];
    }>(),
    {
        hiddenPaths: () => [],
    },
);

const page = usePage();
const collapsed = ref(false);
const lastScrollY = ref(0);
const ticking = ref(false);
const animationFrame = ref<number | null>(null);

const currentPath = computed(() => page.url.split('?')[0] ?? '/');

const hidden = computed(() =>
    props.hiddenPaths.some(
        (path) =>
            currentPath.value === path ||
            currentPath.value.startsWith(`${path}/`),
    ),
);

provide(floatingBarContextKey, {
    collapsed,
    currentPath,
});

const updateCollapsedState = () => {
    ticking.value = false;

    const y = window.scrollY;
    const delta = y - lastScrollY.value;
    const atBottom =
        window.innerHeight + y >= document.documentElement.scrollHeight - 24;

    if (atBottom) {
        collapsed.value = true;
    } else if (Math.abs(delta) >= 6) {
        collapsed.value = delta > 0 && y > 24;
    }

    lastScrollY.value = y;
};

const handleScroll = () => {
    if (ticking.value) {
        return;
    }

    ticking.value = true;
    animationFrame.value = window.requestAnimationFrame(updateCollapsedState);
};

onMounted(() => {
    lastScrollY.value = window.scrollY;
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);

    if (animationFrame.value !== null) {
        window.cancelAnimationFrame(animationFrame.value);
    }
});

watch(
    () => page.url,
    () => {
        collapsed.value = false;
    },
);
</script>

<template>
    <nav
        v-if="!hidden"
        aria-label="Primary"
        :class="
            cn(
                'mobile-tab-bar pointer-events-none fixed inset-x-0 bottom-[calc(env(safe-area-inset-bottom)+1.5rem)] z-50 md:hidden',
                props.class,
            )
        "
    >
        <div
            :class="
                cn(
                    'mx-auto w-fit transform-gpu will-change-transform [backface-visibility:hidden]',
                    'transition-transform duration-[450ms] ease-[cubic-bezier(0.22,1,0.36,1)]',
                )
            "
        >
            <div
                :class="
                    cn(
                        'pointer-events-auto flex w-fit items-center rounded-full p-1.5 [contain:layout_paint]',
                        'border border-white/20 bg-background/70 shadow-lg shadow-black/10 backdrop-blur-xl backdrop-saturate-150 dark:border-white/10',
                    )
                "
            >
                <slot />
            </div>
        </div>
    </nav>
</template>
