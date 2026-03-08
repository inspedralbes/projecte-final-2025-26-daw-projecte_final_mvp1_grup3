import { spawn } from 'child_process';
const server = spawn('node', ['.output/server/index.mjs']);

setTimeout(async () => {
    try {
        const res = await fetch('http://localhost:3000/_nuxt/entry.X_vNdlZB.css');
        console.log("CSS Status: " + res.status);
    } catch(e) {
        console.error(e);
    }
    
    try {
        const res2 = await fetch('http://localhost:3000/_nuxt/non-existent.css');
        console.log("Missing CSS Status: " + res2.status);
    } catch(e) {
        console.error(e);
    }

    server.kill();
}, 2000);
