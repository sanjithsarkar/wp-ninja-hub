<template>
    <div>
        <div class="wpninja-page-header">
            <div class="wpninja-page-icon" style="background:#f1f5f9;color:#475569;">
                <el-icon :size="22"><DocumentCopy /></el-icon>
            </div>
            <div>
                <h1 class="wpninja-page-title">Shortcodes</h1>
                <p class="wpninja-page-subtitle">Use these shortcodes on any page</p>
            </div>
        </div>

        <el-card shadow="hover">
            <template #header>
                <span style="font-weight:600;">WP Ninja Hub Shortcode</span>
            </template>
            <p style="color:#64748b;margin-bottom:16px;">
                Add this shortcode to any page to display the WP Ninja Hub dashboard for logged-in users.
            </p>
            <el-input v-model="shortcode" readonly size="large">
                <template #append>
                    <el-button @click="copy" :type="copied ? 'success' : 'primary'">
                        <el-icon style="margin-right:4px;"><DocumentCopy /></el-icon>
                        {{ copied ? 'Copied!' : 'Copy' }}
                    </el-button>
                </template>
            </el-input>
        </el-card>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { DocumentCopy } from '@element-plus/icons-vue';

const shortcode = ref('[wp_ninja_hub]');
const copied = ref(false);

function copy() {
    const text = shortcode.value;
    const fallbackCopy = () => {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            copied.value = true;
            setTimeout(() => { copied.value = false; }, 2000);
        } catch (e) {
            console.error('Copy failed:', e);
        }
        document.body.removeChild(ta);
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            copied.value = true;
            setTimeout(() => { copied.value = false; }, 2000);
        }).catch(() => fallbackCopy());
    } else {
        fallbackCopy();
    }
}
</script>
