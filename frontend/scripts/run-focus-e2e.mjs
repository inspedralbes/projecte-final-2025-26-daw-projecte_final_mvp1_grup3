import { execSync } from "node:child_process";

const apiUrl = process.env.E2E_API_URL || "http://localhost:8000";

async function checkApi() {
  try {
    const response = await fetch(`${apiUrl}/api/calendar/snapshot/1/1999-01-01`, {
      method: "GET"
    });
    return response.status === 404 || response.ok;
  } catch (e) {
    return false;
  }
}

const ok = await checkApi();
if (!ok) {
  console.error(`[mode-focus e2e] API no disponible en ${apiUrl}.`);
  console.error("[mode-focus e2e] Arranca backend Laravel/Node/Redis y reintenta.");
  process.exit(1);
}

execSync("npx playwright test tests/e2e/mode-focus.spec.js", {
  stdio: "inherit"
});
