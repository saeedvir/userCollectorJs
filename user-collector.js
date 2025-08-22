function collectAndSendUserAnalytics() {
    const ua = navigator.userAgent;
    const screen = window.screen;
    const now = new Date().toISOString();

    const data = {
        os: getOSInfo(ua),
        browser: getBrowserInfo(ua),
        screen: {
            width: screen.width,
            height: screen.height,
            availWidth: screen.availWidth,
            availHeight: screen.availHeight,
            colorDepth: screen.colorDepth,
            pixelRatio: window.devicePixelRatio,
            dpi: Math.round(96 * window.devicePixelRatio)
        },
        touchable: isTouchable(),
        hardware: {
            cpuCores: navigator.hardwareConcurrency || null,
            memory: navigator.deviceMemory || null,
            gpu: getGPUInfo()
        },
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        language: navigator.language,
        languageDirection: document.querySelector("html").getAttribute("dir"),
        online: navigator.onLine,
        referrer: document.referrer,
        timestamp: now,
        userAgent: ua,
        url: window.location.href,
        page: document.title,
        screenOrientation: screen.orientation,
        devicePixelRatio: window.devicePixelRatio,
        colorDepth: screen.colorDepth,
        innerWidth: window.innerWidth,
        innerHeight: window.innerHeight,
        outerWidth: window.outerWidth,
        outerHeight: window.outerHeight,
        isOldCssSupport: isOldCssSupport()
    };

    if (localStorage.getItem('information-collected') !== 'yess') {
        fetch('/api/user-analytics', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data),
            cache: 'no-store'
        }).catch(error => {
            console.warn('Analytics failed:', error.message);
            new Image().src = `/api/user-analytics?data=${encodeURIComponent(JSON.stringify(data))}`;
        });

        localStorage.setItem('information-collected', 'yes');
    }

    return data;
}

function getOSInfo(ua) {
    const match = ua.match(/\(.*?\)/);
    return {
        name: match ? match[0].replace(/[()]/g, '') : 'Unknown OS',
        version: ''
    };
}

function getBrowserInfo(ua) {
    const match = ua.match(/(Chrome|Firefox|Safari|Edge|IE)\/(\d+\.\d+)/);
    return {
        name: match ? match[1] : 'Unknown Browser',
        version: match ? match[2] : ''
    };
}

function getGPUInfo() {
    try {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') ||
            canvas.getContext('experimental-webgl');
        if (!gl) return null;

        const info = gl.getExtension('WEBGL_debug_renderer_info');
        if (!info) return null;

        return {
            vendor: gl.getParameter(info.UNMASKED_VENDOR_WEBGL),
            renderer: gl.getParameter(info.UNMASKED_RENDERER_WEBGL)
        };
    } catch (e) {
        return null;
    }
}

function isTouchable() {
    return (
        'ontouchstart' in window ||
        navigator.maxTouchPoints > 0 ||
        window.matchMedia('(any-pointer: coarse)').matches
    );
}

function isOldCssSupport() {

    // Detect CSS support (modern features)
    if (window.CSS && typeof CSS.supports === "function") {
        // Test for some modern CSS features
        if (
            !CSS.supports("display", "grid") ||      // CSS Grid
            !CSS.supports("color", "var(--test)") || // CSS variables
            !CSS.supports("transform", "translate3d(0,0,0)") // 3D transforms
        ) {
            return true;
        }
    } else {
        // Browser does not support CSS.supports at all (very old)
        return true;
    }

    return false;
}

document.addEventListener('DOMContentLoaded', collectAndSendUserAnalytics, { once: true });
