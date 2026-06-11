import fs from 'node:fs/promises';
import path from 'node:path';

const baseUrl = (process.env.APP_SCREENSHOT_URL ?? 'https://simple-content-planner.test').replace(/\/$/, '');
const email = process.env.APP_SCREENSHOT_EMAIL ?? 'admin@imm.local';
const password = process.env.APP_SCREENSHOT_PASSWORD ?? 'password';
const debugUrl = process.env.CHROME_DEBUG_URL ?? 'http://127.0.0.1:9222';
const outputDirectory = path.resolve('docs/screenshots');

const response = await fetch(`${debugUrl}/json/new?${encodeURIComponent('about:blank')}`, { method: 'PUT' });
if (!response.ok) {
    throw new Error(`Tidak dapat membuat tab Chrome DevTools: ${response.status}`);
}

const target = await response.json();
const socket = new WebSocket(target.webSocketDebuggerUrl);
const pending = new Map();
let commandId = 0;

socket.addEventListener('message', (event) => {
    const message = JSON.parse(event.data);
    if (!message.id || !pending.has(message.id)) {
        return;
    }

    const { resolve, reject } = pending.get(message.id);
    pending.delete(message.id);

    if (message.error) {
        reject(new Error(message.error.message));
    } else {
        resolve(message.result ?? {});
    }
});

await new Promise((resolve, reject) => {
    socket.addEventListener('open', resolve, { once: true });
    socket.addEventListener('error', reject, { once: true });
});

function command(method, params = {}) {
    const id = ++commandId;

    return new Promise((resolve, reject) => {
        pending.set(id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });
}

async function evaluate(expression) {
    const result = await command('Runtime.evaluate', {
        expression,
        awaitPromise: true,
        returnByValue: true,
    });

    if (result.exceptionDetails) {
        throw new Error(result.exceptionDetails.text ?? 'Evaluasi browser gagal.');
    }

    return result.result?.value;
}

async function navigate(url) {
    await command('Page.navigate', { url });
    await waitFor(() => evaluate('document.readyState === "complete"'));
}

async function waitFor(check, timeout = 15000) {
    const startedAt = Date.now();

    while (Date.now() - startedAt < timeout) {
        if (await check()) {
            return;
        }

        await new Promise((resolve) => setTimeout(resolve, 150));
    }

    throw new Error('Batas waktu browser terlewati.');
}

async function screenshot(filename, fullPage = false) {
    let clip;

    if (fullPage) {
        const { contentSize } = await command('Page.getLayoutMetrics');
        clip = {
            x: 0,
            y: 0,
            width: 1600,
            height: Math.ceil(contentSize.height),
            scale: 1,
        };
    }

    const result = await command('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: fullPage,
        fromSurface: true,
        ...(clip ? { clip } : {}),
    });

    await fs.writeFile(path.join(outputDirectory, filename), Buffer.from(result.data, 'base64'));
}

await fs.mkdir(outputDirectory, { recursive: true });
await command('Page.enable');
await command('Runtime.enable');
await command('Emulation.setDeviceMetricsOverride', {
    width: 1600,
    height: 1000,
    deviceScaleFactor: 1,
    mobile: false,
});

await navigate(`${baseUrl}/login`);
await evaluate(`(() => {
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');
    if (!email || !password) return false;
    email.value = ${JSON.stringify(email)};
    password.value = ${JSON.stringify(password)};
    email.dispatchEvent(new Event('input', { bubbles: true }));
    password.dispatchEvent(new Event('input', { bubbles: true }));
    document.querySelector('form').requestSubmit();
    return true;
})()`);

await waitFor(async () => (await evaluate('location.pathname')) === '/brands');
await waitFor(async () => (await evaluate('document.querySelectorAll(".brand-card").length')) > 0);
await evaluate(`localStorage.setItem('imm-theme', 'dark')`);
await command('Page.reload');
await waitFor(() => evaluate('document.readyState === "complete"'));
await waitFor(() => evaluate('Array.from(document.images).every((image) => image.complete)'));
await screenshot('brand-workspaces.png');

const workspaceUrl = await evaluate(`(() => {
    const cards = Array.from(document.querySelectorAll('.brand-card'));
    const populatedCard = cards.find((card) => {
        const match = card.textContent.match(/(\\d+)\\s+konten tersimpan/i);
        return match && Number(match[1]) > 0;
    });

    return (populatedCard ?? cards[0])?.querySelector('.brand-main')?.href;
})()`);
if (!workspaceUrl) {
    throw new Error('Workspace brand tidak ditemukan.');
}

await navigate(workspaceUrl);
await waitFor(() => evaluate('document.querySelector(".planner-shell .main") !== null'));
await waitFor(() => evaluate('Array.from(document.images).every((image) => image.complete)'));
await screenshot('content-planner-workspace.png', true);

await command('Page.close');
socket.close();

console.log('Screenshot dokumentasi berhasil diperbarui.');
