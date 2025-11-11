<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        src="/img/FSF_Logo.png"
        alt="FSF logo"
        class="w-full h-full"
        v-bind="$attrs"
    />
</template>
