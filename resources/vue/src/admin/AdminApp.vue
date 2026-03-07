<template>
    <div class="wpninja-admin-wrap">
        <div class="wpninja-admin-header">
            <div class="wpninja-admin-logo">
                <div class="wpninja-admin-logo-icon">N</div>
                <div>
                    <h1 class="wpninja-admin-title">WP Ninja Hub</h1>
                    <p class="wpninja-admin-subtitle">Unified dashboard for WPManageNinja plugins</p>
                </div>
            </div>
        </div>

        <el-row :gutter="20" style="margin-top: 24px;">
            <!-- Shortcode Card -->
            <el-col :span="12">
                <el-card shadow="hover">
                    <template #header>
                        <div class="card-header">
                            <el-icon :size="20"><DocumentCopy /></el-icon>
                            <span>Shortcode</span>
                        </div>
                    </template>
                    <p class="card-desc">
                        Add this shortcode to any page to display the WP Ninja Hub dashboard. The user must be logged in.
                    </p>
                    <div class="shortcode-box">
                        <el-input
                            v-model="shortcode"
                            readonly
                            size="large"
                        >
                            <template #append>
                                <el-button @click="copyShortcode" :type="copied ? 'success' : 'primary'">
                                    <el-icon style="margin-right: 4px;"><DocumentCopy /></el-icon>
                                    {{ copied ? 'Copied!' : 'Copy' }}
                                </el-button>
                            </template>
                        </el-input>
                    </div>
                </el-card>
            </el-col>

            <!-- Active Plugins Card -->
            <el-col :span="12">
                <el-card shadow="hover">
                    <template #header>
                        <div class="card-header">
                            <el-icon :size="20"><Connection /></el-icon>
                            <span>Active Plugins</span>
                        </div>
                    </template>
                    <div v-if="loading" style="text-align: center; padding: 20px;">
                        <el-icon class="is-loading" :size="24"><Loading /></el-icon>
                    </div>
                    <div v-else-if="plugins.length === 0">
                        <el-empty description="No WPManageNinja plugins detected" :image-size="60" />
                    </div>
                    <div v-else class="plugin-list">
                        <div v-for="p in plugins" :key="p.slug" class="plugin-item">
                            <el-tag :type="pluginTagType(p.slug)" effect="light" size="large" round>
                                {{ p.label }}
                            </el-tag>
                        </div>
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <el-row :gutter="20" style="margin-top: 20px;">
            <!-- Quick Guide -->
            <el-col :span="24">
                <el-card shadow="hover">
                    <template #header>
                        <div class="card-header">
                            <el-icon :size="20"><InfoFilled /></el-icon>
                            <span>Quick Guide</span>
                        </div>
                    </template>
                    <el-steps :active="3" finish-status="success" align-center>
                        <el-step title="Install" description="Activate WPManageNinja plugins" />
                        <el-step title="Add Shortcode" description="Paste [wp_ninja_hub] on any page" />
                        <el-step title="Done" description="Logged-in users see the dashboard" />
                    </el-steps>
                </el-card>
            </el-col>
        </el-row>

        <el-row :gutter="20" style="margin-top: 20px;">
            <el-col :span="24">
                <el-card shadow="hover">
                    <template #header>
                        <div class="card-header">
                            <el-icon :size="20"><Monitor /></el-icon>
                            <span>Preview</span>
                        </div>
                    </template>
                    <p class="card-desc" v-if="dashboardUrl">
                        View the live dashboard on the front-end:
                    </p>
                    <p class="card-desc" v-else>
                        No page found with the <code>[wp_ninja_hub]</code> shortcode. Create a page and add the shortcode to get started.
                    </p>
                    <el-button
                        v-if="dashboardUrl"
                        type="primary"
                        @click="openDashboard"
                        plain
                    >
                        <el-icon style="margin-right: 4px;"><Monitor /></el-icon>
                        Open Dashboard
                    </el-button>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { DocumentCopy, Connection, InfoFilled, Loading, Monitor } from '@element-plus/icons-vue';

const config = window.wpNinjaHub || {};
const shortcode = ref('[wp_ninja_hub]');
const copied = ref(false);
const plugins = ref([]);
const loading = ref(true);
const dashboardUrl = ref(config.dashboardUrl || '');

function copyShortcode() {
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

function openDashboard() {
    window.open(dashboardUrl.value, '_blank');
}

function pluginTagType(slug) {
    const map = {
        paymattic: '',
        fluentform: 'success',
        fluentcrm: 'warning',
        fluentboard: 'danger',
        fluentcommunity: 'info',
    };
    return map[slug] || '';
}

onMounted(async () => {
    try {
        const response = await fetch(config.restUrl + 'menu', {
            headers: { 'X-WP-Nonce': config.nonce },
        });
        if (response.ok) {
            plugins.value = await response.json();
        }
    } catch (e) {
        console.error('Failed to load plugins:', e);
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.wpninja-admin-wrap {
    max-width: 960px;
    margin: 20px 20px 20px 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, sans-serif;
}

.wpninja-admin-header {
    margin-bottom: 8px;
}

.wpninja-admin-logo {
    display: flex;
    align-items: center;
    gap: 14px;
}

.wpninja-admin-logo-icon {
    width: 44px;
    height: 44px;
    background: #3b82f6;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 20px;
}

.wpninja-admin-title {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    padding: 0;
    line-height: 1.3;
}

.wpninja-admin-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 2px 0 0;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 15px;
}

.card-desc {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 16px;
    line-height: 1.5;
}

.card-desc code {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
}

.shortcode-box {
    margin-top: 8px;
}

.plugin-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.plugin-item {
    display: inline-block;
}
</style>
