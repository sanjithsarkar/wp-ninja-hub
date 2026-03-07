# WP Ninja Hub প্লাগিনে Vite কীভাবে কাজ করে — বিস্তারিত বাংলা গাইড

এই ডকুমেন্টটি বিগিনারদের জন্য। প্রতিটি ধাপ এবং গুরুত্বপূর্ণ লাইন বাংলায় ব্যাখ্যা করা হয়েছে।

---

## ১. Vite কী?

**Vite** হলো একটা **ফ্রন্টএন্ড বিল্ড টুল**। এর কাজ:

- আপনার **Vue.js** (অথবা React ইত্যাদি) কোডকে **বান্ডল** করা (এক বা একাধিক জাভাস্ক্রিপ্ট/সিএসএস ফাইলে জমা করা)
- **ডেভেলপমেন্ট** সময়ে দ্রুত রান করা এবং **Hot Module Replacement (HMR)** দিয়ে কোড বদলালেই ব্রাউজার অটো আপডেট
- **প্রডাকশন** এর জন্য ছোট ও অপটিমাইজড ফাইল বানানো

এই প্লাগিনে Vite দিয়ে **Vue 3 + Element Plus** অ্যাপ বিল্ড করা হয় এবং সেটা WordPress ড্যাশবোর্ড ও শর্টকোড পেজে লোড করা হয়।

---

## ২. প্রজেক্টে Vite-সম্পর্কিত ফাইল কাঠামো

```
wp-ninja-hub/
├── package.json          ← npm স্ক্রিপ্ট ও ডিপেন্ডেন্সি
├── vite.config.js       ← Vite কনফিগারেশন (কোথে বিল্ড হবে, কী কী এন্ট্রি)
├── resources/vue/src/
│   ├── main.js          ← ফ্রন্টএন্ড (শর্টকোড) অ্যাপের এন্ট্রি
│   ├── admin/main.js    ← অ্যাডমিন ড্যাশবোর্ড অ্যাপের এন্ট্রি
│   ├── App.vue
│   ├── admin/AdminApp.vue
│   └── ...
├── assets/dist/         ← Vite বিল্ড করার পর এখানে ফাইল তৈরি হয়
│   ├── .vite/manifest.json
│   └── assets/
│       ├── main.js
│       ├── main.css
│       ├── admin.js
│       └── admin.css
└── src/Controllers/
    ├── DashboardController.php   ← শর্টকোড পেজে JS/CSS লোড করে
    └── AdminController.php       ← অ্যাডমিন পেজে JS/CSS লোড করে
```

---

## ৩. package.json — লাইন বাই লাইন

```json
{
    "name": "wp-ninja-hub",
    "private": true,
    "scripts": {
        "dev": "vite",
        "build": "vite build"
    },
    "dependencies": {
        "@element-plus/icons-vue": "^2.3.2",
        "element-plus": "^2.13.4",
        "vue": "^3.4"
    },
    "devDependencies": {
        "@vitejs/plugin-vue": "^5.0",
        "vite": "^5.0"
    }
}
```

### ব্যাখ্যা

| লাইন / অংশ | মানে কী |
|------------|--------|
| `"name"` | প্রজেক্টের নাম। npm-এ পাবলিশ করলে এই নাম ব্যবহার হয়। |
| `"private": true` | এই প্রজেক্ট npm-এ পাবলিশ হবে না। |
| `"scripts"` | টার্মিনালে চালানোর কমান্ড। |
| `"dev": "vite"` | `npm run dev` চালালে **Vite ডেভেলপমেন্ট সার্ভার** চালু হয় (পোর্ট ৫১৭৩)। তখন ফাইল সরাসরি সোর্স থেকে সার্ভ হয়, HMR চালু থাকে। |
| `"build": "vite build"` | `npm run build` চালালে Vite **প্রডাকশন বিল্ড** করে। তখন `assets/dist` এর ভিতরে `main.js`, `main.css`, `admin.js`, `admin.css` ইত্যাদি তৈরি হয়। |
| `dependencies` | অ্যাপ রানটাইমে দরকার (Vue, Element Plus, আইকন)। |
| `devDependencies` | শুধু ডেভেলপমেন্ট ও বিল্ড সময়ে দরকার (Vite, Vue প্লাগিন)। |

### সংক্ষেপে দুটো কমান্ড

- **ডেভেলপমেন্ট:** `npm run dev` → লোকাল সার্ভার চালু, কোড এডিট করলে পেজ অটো রিফ্রেশ।
- **প্রডাকশন বিল্ড:** `npm run build` → `assets/dist` এ জেএস/সিএসএস ফাইল তৈরি, ওই ফাইলগুলো PHP দিয়ে WordPress-এ লোড করা হয়।

---

## ৪. vite.config.js — লাইন বাই লাইন

```javascript
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    server: {
        port: 5173,
        strictPort: true,
        cors: true,
    },
    build: {
        outDir: resolve(__dirname, 'assets/dist'),
        emptyOutDir: true,
        manifest: true,
        cssCodeSplit: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'resources/vue/src/main.js'),
                admin: resolve(__dirname, 'resources/vue/src/admin/main.js'),
            },
            output: {
                entryFileNames: 'assets/[name].js',
                chunkFileNames: 'assets/[name]-chunk.js',
                assetFileNames: 'assets/[name].[ext]',
            },
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/vue/src'),
        },
    },
});
```

### লাইন বাই লাইন ব্যাখ্যা

| লাইন | কী করে |
|------|--------|
| `import { defineConfig } from 'vite'` | Vite থেকে কনফিগ লিখার হেল্পার ফাংশন নেয়। |
| `import vue from '@vitejs/plugin-vue'` | Vue ফাইল (.vue) যাতে Vite বুঝতে পারে তার প্লাগিন। |
| `import { resolve } from 'path'` | ফোল্ডার পাথ সঠিকভাবে বানাতে Node.js এর `path.resolve`। |
| `export default defineConfig({ ... })` | Vite কে বলছে: “এই অবজেক্টটাই আমার কনফিগ।” |
| `plugins: [vue()]` | Vue SFC (.vue) সাপোর্ট চালু। |
| `server: { ... }` | শুধু **ডেভ সার্ভার** এর সেটিং (npm run dev)। |
| `port: 5173` | ডেভ সার্ভার এই পোর্টে চালু হবে (http://localhost:5173)। |
| `strictPort: true` | ৫১৭৩ ব্যস্ত থাকলে অন্য পোর্ট নেবে না, এরর দেবে। |
| `cors: true` | অন্য ডোমেইন (যেমন আপনার WordPress সাইট) থেকে এই সার্ভারে রিকোয়েস্ট পাঠানো যাবে। |
| `build: { ... }` | শুধু **বিল্ড** এর সেটিং (npm run build)। |
| `outDir: resolve(__dirname, 'assets/dist')` | বিল্ডের আউটপুট যাবে প্লাগিনের `assets/dist` ফোল্ডারে। |
| `emptyOutDir: true` | প্রতিবার বিল্ডের আগে `assets/dist` খালি করে দেবে। |
| `manifest: true` | `assets/dist/.vite/manifest.json` বানাবে; এতে কোন এন্ট্রি কোন ফাইলে গেছে তার ম্যাপ থাকে। |
| `cssCodeSplit: true` | প্রতিটি এন্ট্রির জন্য আলাদা সিএসএস ফাইল (main.css, admin.css)। |
| `rollupOptions.input` | **এন্ট্রি পয়েন্ট** — কোন জেএস ফাইল দিয়ে বিল্ড শুরু হবে। |
| `main: resolve(..., 'resources/vue/src/main.js')` | ফ্রন্টএন্ড (শর্টকোড) অ্যাপের এন্ট্রি। |
| `admin: resolve(..., 'resources/vue/src/admin/main.js')` | অ্যাডমিন ড্যাশবোর্ড অ্যাপের এন্ট্রি। |
| `entryFileNames: 'assets/[name].js'` | এন্ট্রি ফাইল নাম: main.js, admin.js (assets ফোল্ডারের ভিতরে)। |
| `chunkFileNames` | শেয়ারড/লাজি কোডের চাঙ্ক ফাইল নাম। |
| `assetFileNames` | ছবি/ফন্ট ইত্যাদি অ্যাসেটের নাম। |
| `resolve.alias` | `@` দিয়ে `resources/vue/src` বোঝায়; যেমন `import X from '@/components/X.vue`। |

### সংক্ষেপে

- **ডেভ:** পোর্ট ৫১৭৩, CORS চালু, Vue প্লাগিন দিয়ে .vue ফাইল চালে।
- **বিল্ড:** দুটো এন্ট্রি (main, admin) থেকে বিল্ড হয়, আউটপুট `assets/dist` এ, manifest ও সিএসএস স্প্লিট চালু।

---

## ৫. এন্ট্রি ফাইল কী করে — main.js ও admin/main.js

### resources/vue/src/main.js (শর্টকোড / ফ্রন্টএন্ড)

```javascript
import { createApp } from 'vue';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import App from './App.vue';
import './style.css';

const el = document.getElementById('wp-ninja-hub-app');
if (el) {
    const app = createApp(App);
    app.use(ElementPlus);
    app.mount(el);
}
```

| লাইন | ব্যাখ্যা |
|------|----------|
| `createApp`, `App`, `app.use`, `app.mount` | Vue 3 অ্যাপ বানিয়ে একটা HTML এলিমেন্টে মাউন্ট করা। |
| `document.getElementById('wp-ninja-hub-app')` | যে div-এ শর্টকোড অ্যাপ লাগবে তার id। PHP পেজে এই id দিয়ে একটা div থাকতে হবে। |
| `app.use(ElementPlus)` | Element Plus UI লাইব্রেরি পুরো অ্যাপে ব্যবহার। |
| CSS import গুলো | Element Plus ও কাস্টম স্টাইল লোড। |

অর্থাৎ এই ফাইলটা **শর্টকোড পেজের** Vue অ্যাপ চালু করে।

### resources/vue/src/admin/main.js (অ্যাডমিন ড্যাশবোর্ড)

```javascript
import { createApp } from 'vue';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import AdminApp from './AdminApp.vue';

const el = document.getElementById('wp-ninja-hub-admin-app');
if (el) {
    const app = createApp(AdminApp);
    app.use(ElementPlus);
    app.mount(el);
}
```

এখানে শুধু **AdminApp** এবং id **wp-ninja-hub-admin-app** ব্যবহার হয়। বাকি লজিক একই — অ্যাডমিন পেজের জন্য আলাদা Vue অ্যাপ।

---

## ৬. WordPress (PHP) থেকে Vite কীভাবে ব্যবহার হয়

প্লাগিন **দুইটা মোড** সাপোর্ট করে:

1. **ডেভ মোড:** Vite ডেভ সার্ভার চালু (localhost:5173)। স্ক্রিপ্ট ও স্টাইল সরাসরি ওই সার্ভার থেকে লোড হয়, HMR কাজ করে।
2. **প্রডাকশন মোড:** আগে `npm run build` চালিয়ে `assets/dist` এ ফাইল বানানো হয়। PHP সেই বিল্ড করা ফাইলগুলো লোড করে।

### ডেভ মোড চেক — isViteDevServer()

`DashboardController.php` ও `AdminController.php` দুজায় একই ধরণের ফাংশন আছে:

```php
private function isViteDevServer(): bool
{
    if (defined('WPNINJA_HUB_DEV') && WPNINJA_HUB_DEV) {
        $response = @file_get_contents('http://localhost:5173/@vite/client');
        return $response !== false;
    }
    return false;
}
```

| অংশ | মানে |
|-----|------|
| `WPNINJA_HUB_DEV` | wp-config.php বা কোথাও define করলে “ডেভ মোড চালু”। |
| `http://localhost:5173/@vite/client` | Vite ডেভ সার্ভারে HMR ক্লায়েন্টের রিকোয়েস্ট। |
| `$response !== false` | এই URL থেকে কিছু পেলে ধরে নেয় Vite সার্ভার চালু আছে। |

অর্থাৎ: **ডেভ মোড চালু আছে কিনা** সেটা PHP এই ফাংশন দিয়ে চেক করে।

### ডেভ মোডে অ্যাসেট লোড (উদাহরণ: DashboardController)

```php
if ($this->isViteDevServer()) {
    add_action('wp_head', function () {
        echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
    });
    // ...
    add_action('wp_footer', function () {
        echo '<script type="module" src="http://localhost:5173/resources/vue/src/main.js"></script>';
    });
    return;
}
```

- প্রথম স্ক্রিপ্ট: Vite এর **HMR ক্লায়েন্ট** (ব্রাউজারকে Vite সার্ভারের সাথে যুক্ত করে)。
- দ্বিতীয় স্ক্রিপ্ট: **এন্ট্রি** — `main.js`। Vite সার্ভার এই পাথটা রেজলভ করে এবং এর সব import (Vue, App, CSS) দেয়।

ডেভ মোডে কোনো বিল্ড ফাইল লাগে না; সব কিছু localhost:5173 থেকে আসে।

### প্রডাকশন মোডে অ্যাসেট লোড

```php
$distUrl = WPNINJA_HUB_URL . 'assets/dist/assets/';

wp_enqueue_style('wpninja-hub-css', $distUrl . 'main.css', ...);

add_action('wp_footer', function () use ($distUrl) {
    echo '<script type="module" src="' . esc_url($distUrl . 'main.js') . '"></script>';
});
```

এখানে সরাসরি **বিল্ড করা** ফাইল ব্যবহার হয়:

- `main.css` → Vite যেটা বানিয়েছে সেটাই।
- `main.js` → বান্ডল করা জাভাস্ক্রিপ্ট।

অ্যাডমিন পেজে একই নিয়মে `admin.js` ও `admin.css` ব্যবহার হয় (AdminController এ)।

---

## ৭. পুরো ফ্লো — স্টেপ বাই স্টেপ

### ক. ডেভেলপমেন্ট (কোড এডিট করার সময়)

1. টার্মিনালে প্লাগিন ফোল্ডারে গিয়ে চালান: **`npm run dev`**
2. Vite ডেভ সার্ভার **http://localhost:5173** এ চালু হয়।
3. wp-config.php (বা যেখানে প্লাগিন সেট করে) তে **`define('WPNINJA_HUB_DEV', true);`** রাখুন।
4. ব্রাউজারে সেই পেজ খুলুন যেখানে শর্টকোড বা অ্যাডমিন ড্যাশবোর্ড আছে।
5. PHP `isViteDevServer()` দিয়ে চেক করে দেখে ৫১৭৩ এ সার্ভার আছে।
6. পেজে লোড হয়:
   - `http://localhost:5173/@vite/client` (HMR)
   - `http://localhost:5173/resources/vue/src/main.js` (অথবা admin/main.js)
7. আপনি .vue বা .js ফাইল এডিট করলে Vite পরিবর্তন টের পেয়ে ব্রাউজারকে আপডেট করে (HMR)।

### খ. প্রডাকশন বিল্ড (লাইভ সাইট / বিনা ডেভ সার্ভার)

1. টার্মিনালে চালান: **`npm run build`**
2. Vite `vite.config.js` অনুযায়ী:
   - `main.js` ও `admin/main.js` দিয়ে বিল্ড শুরু করে
   - আউটপুট `assets/dist` এ জমা করে (main.js, main.css, admin.js, admin.css ইত্যাদি)
   - `.vite/manifest.json` বানায়
3. সাইটে **WPNINJA_HUB_DEV** বন্ধ রাখুন (অথবা define করবেন না)।
4. ইউজার পেজ ওপেন করলে PHP আর localhost:5173 চেক করে না; সরাসরি `assets/dist/assets/main.js` ও `main.css` (অথবা admin) লোড করে।

---

## ৮. বিল্ডের পর কী কী ফাইল তৈরি হয়

`npm run build` চালানোর পর সাধারণত এমন হয়:

```
assets/dist/
├── .vite/
│   └── manifest.json     ← কোন এন্ট্রি কোন ফাইলে ম্যাপ করা আছে
└── assets/
    ├── main.js          ← শর্টকোড অ্যাপ (বান্ডল)
    ├── main.css         ← শর্টকোড অ্যাপের স্টাইল
    ├── admin.js         ← অ্যাডমিন অ্যাপ (বান্ডল)
    ├── admin.css        ← অ্যাডমিন অ্যাপের স্টাইল
    └── _plugin-vue_export-helper-chunk.js  (এবং সংশ্লিষ্ট .css)  ← Vue কম্পাইলার হেল্পার
```

PHP শুধু **main.js / main.css** অথবা **admin.js / admin.css** লোড করে; বাকি চাঙ্কগুলো main.js বা admin.js এর ভিতর থেকে অটো লোড হয়।

---

## ৯. সংক্ষিপ্ত সারণি

| প্রশ্ন | উত্তর |
|--------|--------|
| Vite কী? | ফ্রন্টএন্ড বিল্ড টুল; Vue বান্ডল ও ডেভ সার্ভার দেয়। |
| ডেভ সার্ভার চালু কী দিয়ে? | `npm run dev` |
| বিল্ড কী দিয়ে? | `npm run build` |
| বিল্ড আউটপুট কোথায়? | `assets/dist` (ভিতরে `assets/` ফোল্ডার)। |
| এন্ট্রি কয়টা? | দুটো — main.js (শর্টকোড), admin/main.js (অ্যাডমিন)। |
| ডেভ মোডে স্ক্রিপ্ট কোথা থেকে? | http://localhost:5173 (Vite সার্ভার)। |
| প্রডাকশনে স্ক্রিপ্ট কোথা থেকে? | প্লাগিনের `assets/dist/assets/` (main.js, admin.js)। |
| ডেভ মোড চালু হয় কখন? | যখন `WPNINJA_HUB_DEV` define করা এবং localhost:5173 এ Vite চালু। |

---

## ১০. নতুনদের জন্য টিপস

1. **প্রথমবার:** প্লাগিন ফোল্ডারে `npm install` চালান, তারপর `npm run build` দিয়ে একবার বিল্ড দেখে নিন।
2. **ডেভ করার সময়:** এক টার্মিনালে `npm run dev` চালু রাখুন; অন্যদিকে ব্রাউজারে পেজ রিফ্রেশ করে দেখুন।
3. **প্রডাকশনে:** `npm run build` চালান, `WPNINJA_HUB_DEV` বন্ধ রাখুন, তারপর পেজ লোড করুন।
4. **এন্ট্রি বদলালে:** নতুন .js এন্ট্রি যোগ করতে হলে শুধু vite.config.js এর `build.rollupOptions.input` এ যোগ করবেন; PHP তে সেই নতুন ফাইল লোড করার লজিক যোগ করতে হবে।

এই ডকুমেন্ট দিয়ে আপনি এই প্লাগিনে Vite কীভাবে কাজ করছে সেটা লাইন বাই লাইন এবং স্টেপ বাই স্টেপ বুঝতে পারবেন। আরও প্রশ্ন থাকলে সেই অংশের ফাইল নাম ও লাইন নম্বর দিয়ে জিজ্ঞেস করতে পারেন।
