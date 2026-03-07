# প্রিভিউ দুটো অপশন — প্ল্যান ও বর্তমান ইমপ্লিমেন্ট (বাংলা)

অ্যাডমিনের **Preview** সেকশনে দুটো অপশন রাখার প্ল্যান এবং বর্তমানে কীভাবে ইমপ্লিমেন্ট করা হয়েছে।

---

## লক্ষ্য

১. **শর্টকোড পেজ প্রিভিউ** — যে পেজে `[wp_ninja_hub]` শর্টকোড দেওয়া আছে, সেই ফ্রন্টএন্ড পেজের লিংক দিয়ে প্রিভিউ।  
২. **ডাইরেক্ট প্রিভিউ (অ্যাডমিন)** — শর্টকোড ছাড়াই অ্যাডমিনের ভিতরেই একই ড্যাশবোর্ড দেখানোর লিংক, যাতে অ্যাডমিন সরাসরি টেস্ট করতে পারে।

**নোট:** সাব-মেনু ব্যবহার করা হয়নি। একই **WP Ninja Hub** পেজেই `preview=1` দিয়ে ডাইরেক্ট প্রিভিউ খোলা হয়।

---

## বর্তমান ইমপ্লিমেন্ট (কীভাবে কাজ করছে)

### ১. একই মেনু পেজ, দুটো মোড

- **WP Ninja Hub** মেনুতে একটা পেজই আছে — কোনো সাব-মেনু নেই।
- এই পেজ দুইভাবে রেন্ডার হয়:
  - **সাধারণ মোড:** `admin.php?page=wp-ninja-hub` — Shortcode, Active Plugins, Quick Guide, Preview কার্ড (AdminApp.vue)।
  - **প্রিভিউ মোড:** `admin.php?page=wp-ninja-hub&preview=1` — শুধু ড্যাশবোর্ড (একই Vue অ্যাপ যেটা শর্টকোড পেজে চলে)।

### ২. AdminController লজিক

| অংশ | কাজ |
|-----|-----|
| `addMenu()` | শুধু `add_menu_page('wp-ninja-hub', ...)` — সাব-মেনু নেই। |
| `admin_enqueue_scripts` | পেজ লোডের সময় চেক: `$_GET['preview'] === '1'` থাকলে `enqueueDashboardAssetsForPreview()` নাহলে `enqueueAdminAssets()`। |
| `renderPage()` | `$_GET['preview'] === '1'` থাকলে `preview-page.php` নাহলে `shortcode-page.php`। |
| `enqueueDashboardAssetsForPreview()` | প্রিভিউ মোডে main.js + main.css (ড্যাশবোর্ড অ্যাসেট) লোড করে। |
| `enqueueAdminAssets()` | সাধারণ মোডে admin.js + admin.css + `window.wpNinjaHub` (restUrl, nonce, dashboardUrl, **directPreviewUrl**) লোড করে। |

### ৩. directPreviewUrl

- **মান:** `admin_url('admin.php?page=wp-ninja-hub&preview=1')`
- **কোথায় সেট হয়:** `enqueueAdminAssets()` এর ভিতরে `window.wpNinjaHub` অবজেক্টে।
- **কেন এখানে:** এই মেথডই রান হয় যখন মূল অ্যাডমিন পেজ (AdminApp.vue) লোড হয়। AdminApp.vue কে ডাইরেক্ট প্রিভিউ বাটনের লিংক দিতে এই URL লাগে।

### ৪. Preview কার্ড (AdminApp.vue)

- **বর্ণনা:** “Preview the dashboard in two ways: on a page with the shortcode, or directly in admin without a shortcode.”
- **বাটন ১ — Shortcode page preview:** শুধু যখন `dashboardUrl` থাকবে (কোনো পেজে শর্টকোড আছে)। ক্লিক করলে সেই ফ্রন্ট পেজ নতুন ট্যাবে ওপেন।
- **বাটন ২ — Direct preview (admin):** সবসময়। ক্লিক করলে `directPreviewUrl` অর্থাৎ `admin.php?page=wp-ninja-hub&preview=1` নতুন ট্যাবে ওপেন।
- শর্টকোড পেজ না থাকলে নিচে হিন্ট: “No page found with [wp_ninja_hub]. Create a page and add the shortcode to use ‘Shortcode page preview’.”

### ৫. ভিউ ও অ্যাসেট

- **preview-page.php:** শুধু `<div id="wp-ninja-hub-app" ...></div>` — এখানে ড্যাশবোর্ড Vue অ্যাপ মাউন্ট হয়।
- **প্রিভিউ মোডে অ্যাসেট:** main.js, main.css (ড্যাশবোর্ডের), Vite dev ও production দুটো সাপোর্ট।

---

## সংক্ষিপ্ত চেকলিস্ট (আপডেটেড)

| ধাপ | কাজ | কোথায় / কীভাবে |
|-----|-----|------------------|
| ১ | সাব-মেনু **না** দেওয়া; একই পেজে `preview=1` দিয়ে প্রিভিউ | AdminController::addMenu() — শুধু মেইন মেনু |
| ২ | পেজ লোডে preview চেক করে ভিউ ও অ্যাসেট সিদ্ধান্ত | AdminController::renderPage(), admin_enqueue_scripts callback |
| ৩ | directPreviewUrl config এ পাঠানো | enqueueAdminAssets() → window.wpNinjaHub.directPreviewUrl |
| ৪ | Preview কার্ডে দুটো বাটন + টেক্সট | AdminApp.vue |
| ৫ | preview-page.php টেমপ্লেট | src/Views/admin/preview-page.php |
| ৬ | প্রিভিউ মোডে ড্যাশবোর্ড অ্যাসেট | enqueueDashboardAssetsForPreview() |

---

## ফলাফল

- **শর্টকোড পেজ প্রিভিউ:** “Shortcode page preview” বাটনে ক্লিক করলে যে পেজে `[wp_ninja_hub]` আছে সেখানে যায়।
- **ডাইরেক্ট প্রিভিউ:** “Direct preview (admin)” বাটনে ক্লিক করলে একই অ্যাডমিন পেজ `?preview=1` দিয়ে খুলে শুধু ড্যাশবোর্ড দেখায়; আলাদা সাব-মেনু বা পেজ নেই।

এই ডকুমেন্ট অনুযায়ী বর্তমান প্রিভিউ অপশন দুটো চালু আছে এবং সাব-মেনু ছাড়াই ইমপ্লিমেন্ট করা হয়েছে।
