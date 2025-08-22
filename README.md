# User Analytics Collection Script

A lightweight JavaScript utility to collect essential user device, browser, and environment information and send it securely to an analytics endpoint. Designed for websites that want to understand their audience's technical setup without relying on third-party trackers.

---

## 📌 Features

- **Comprehensive Device & Browser Detection**: OS, browser name/version, screen specs, hardware concurrency, and more.
- **Privacy-Conscious**: Data is collected only once per session using `localStorage`.
- **CSRF-Secure**: Uses CSRF token from meta tag for secure transmission.
- **Fallback Support**: Falls back to image-based tracking if `fetch()` fails (e.g., ad blockers).
- **Touch Detection**: Detects if the device supports touch input.
- **GPU Info (WebGL)**: Retrieves GPU vendor and renderer via WebGL when available.
- **Timezone & Language**: Includes user timezone, language, and text direction.
- **Network & Navigation**: Captures online status, referrer, page title, and URL.

---

## 🛠️ How It Works

The script runs once after the DOM is fully loaded and:

1. Gathers detailed client-side information.
2. Sends it via a secure `POST` request to `/api/user-analytics`.
3. Stores a flag in `localStorage` to prevent duplicate submissions.
4. Falls back to GET via `<img src>` if the fetch fails.

---

## 🧩 Collected Data

| Category       | Fields Collected |
|----------------|------------------|
| **OS**         | Name (from User Agent), Version |
| **Browser**    | Name, Version |
| **Screen**     | Width, Height, Available dimensions, Color depth, Pixel ratio, DPI |
| **Device**     | Touch support, CPU cores, Device memory, GPU (vendor/renderer) |
| **User Locale**| Timezone, Language, Text direction (`ltr`/`rtl`) |
| **Network**    | Online status, Referrer |
| **Page**       | URL, Title, Inner/outer dimensions |
| **System**     | User Agent, Timestamp, Screen orientation |

---

## 🔐 Security & Privacy

- ✅ Respects user privacy: collects only non-personally identifiable technical data.
- ✅ Uses `localStorage` to ensure **only one submission per session**.
- ✅ Requires a CSRF token for security (common in frameworks like Laravel, Django, etc.).
- ❗ Ensure your backend validates and sanitizes incoming data.

> 💡 You can customize the storage key (`information-collected`) or disable it for testing.

---

## ⚙️ Setup Instructions

### 1. Include CSRF Token (if required)
Make sure your HTML includes a CSRF token meta tag:
```html
<meta name="csrf-token" content="your-csrf-token-here">
```

### 2. Add the Script
Place this script at the end of your `<body>` or load it asynchronously:

```html
<script src="user-analytics.js"></script>
```

Or inline:
```html
<script>
  // Paste the full script here
</script>
```

### 3. Set Up Backend Endpoint
Configure your server to accept POST requests at `/api/user-analytics`:

```js
// Example Express.js route (Node.js)
app.post('/api/user-analytics', (req, res) => {
  const analyticsData = req.body;
  // Save to DB, log, or process
  console.log(analyticsData);
  res.status(200).send('OK');
});
```

> If using the fallback image method, also support GET with `data` param.

---

## 🔄 Fallback Mechanism

If `fetch()` fails (e.g., blocked by ad blocker or CORS), it falls back to:
```js
new Image().src = `/api/user-analytics?data=${encodeURIComponent(JSON.stringify(data))}`;
```
This ensures data delivery even in restricted environments.

---

## 🛑 Limitations

- **GPU Info**: Only available if WebGL is supported and enabled.
- **Hardware Info**: `deviceMemory` and `hardwareConcurrency` may be limited in some browsers for privacy.
- **User Agent Parsing**: Basic regex-based parsing; may not catch all edge cases.

---

## 📄 License

MIT — Feel free to use, modify, and distribute.

---

## 📬 Feedback & Contributions

Open an issue or submit a PR on GitHub. Contributions welcome!

> ✉️ Maintainer: [Your Name]  
> 🌐 GitHub: `https://github.com/your-username/user-analytics`

---

```text
💡 Pro Tip: Combine this with backend aggregation to generate insights about your user base — 
like most common screen sizes, browser versions, or regional timezones.
```
