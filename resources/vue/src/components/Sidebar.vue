<template>
    <div class="wpninja-sidebar">
        <div class="wpninja-sidebar-brand">
            <span class="wpninja-sidebar-brand-name">WP Ninja Hub</span>
            <span class="wpninja-sidebar-brand-avatar">N</span>
        </div>

        <div class="wpninja-sidebar-divider"></div>

        <ul class="wpninja-sidebar-nav">
            <li>
                <button :class="{ active: active === 'dashboard' }" @click="$emit('select', 'dashboard')">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <rect x="1" y="1" width="6.5" height="6.5" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                            <rect x="10.5" y="1" width="6.5" height="6.5" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                            <rect x="1" y="10.5" width="6.5" height="6.5" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                            <rect x="10.5" y="10.5" width="6.5" height="6.5" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                        </svg>
                    </span>
                    Dashboard
                    <span class="nav-arrow">&rsaquo;</span>
                </button>
            </li>
            <li v-for="item in items" :key="item.slug">
                <button :class="{ active: item.slug === active }" @click="$emit('select', item.slug)">
                    <span class="nav-icon" v-html="icons[item.slug]"></span>
                    {{ item.label }}
                    <span class="nav-arrow">&rsaquo;</span>
                </button>
            </li>
            <li>
                <button :class="{ active: active === 'shortcodes' }" @click="$emit('select', 'shortcodes')">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <polyline points="5.5,4 1.5,9 5.5,14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            <polyline points="12.5,4 16.5,9 12.5,14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </svg>
                    </span>
                    Shortcodes
                    <span class="nav-arrow">&rsaquo;</span>
                </button>
            </li>
        </ul>

        <div class="wpninja-sidebar-footer">
            <span>WP Ninja Hub</span>
            <span>Version 1.0.0</span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    items: { type: Array, default: () => [] },
    active: { type: String, default: '' },
    loading: { type: Boolean, default: false },
});

defineEmits(['select']);

const icons = {
    paymattic: `<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
        <rect x="2" y="3" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/>
        <path d="M2 7h14" stroke="currentColor" stroke-width="1.6"/>
        <path d="M5 11h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    </svg>`,
    fluentform: `<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
        <rect x="2" y="1.5" width="14" height="15" rx="2" stroke="currentColor" stroke-width="1.6"/>
        <path d="M5.5 5.5h7M5.5 9h5M5.5 12.5h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    </svg>`,
    fluentcrm: `<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
        <circle cx="7" cy="6.5" r="3" stroke="currentColor" stroke-width="1.6"/>
        <path d="M1.5 15.5c0-3 2.5-5 5.5-5s5.5 2 5.5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        <circle cx="13.5" cy="5.5" r="2" stroke="currentColor" stroke-width="1.6"/>
        <path d="M14 10c1.5.5 2.5 1.8 2.5 3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    </svg>`,
    fluentboard: `<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
        <rect x="1.5" y="2" width="15" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/>
        <rect x="4" y="5" width="3" height="4" rx="0.8" stroke="currentColor" stroke-width="1.2"/>
        <rect x="7.5" y="5" width="3" height="6" rx="0.8" stroke="currentColor" stroke-width="1.2"/>
        <rect x="11" y="5" width="3" height="3" rx="0.8" stroke="currentColor" stroke-width="1.2"/>
    </svg>`,
    fluentcommunity: `<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
        <path d="M2 3.5A1.5 1.5 0 013.5 2h8A1.5 1.5 0 0113 3.5v6a1.5 1.5 0 01-1.5 1.5H6l-2.5 2.5V11H3.5A1.5 1.5 0 012 9.5v-6z" stroke="currentColor" stroke-width="1.6"/>
        <path d="M14 6h1a1.5 1.5 0 011.5 1.5v6a1.5 1.5 0 01-1.5 1.5h-.5v2.5L12 15h-3a1.5 1.5 0 01-1.5-1.5V13" stroke="currentColor" stroke-width="1.4"/>
    </svg>`,
};
</script>
