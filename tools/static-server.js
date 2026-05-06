#!/usr/bin/env node

const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const Core = require('../public/assets/js/api-core.js');

const root = path.resolve(__dirname, '..');
const port = Number(process.env.PORT || 5173);

const mimeTypes = {
  '.css': 'text/css; charset=utf-8',
  '.html': 'text/html; charset=utf-8',
  '.ico': 'image/x-icon',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.png': 'image/png',
  '.svg': 'image/svg+xml',
  '.webp': 'image/webp',
};

function resolveRequestPath(requestUrl) {
  const url = new URL(requestUrl, `http://127.0.0.1:${port}`);
  const routePath = Core.resolveStaticRoute(url.pathname);
  const pathname = routePath || decodeURIComponent(url.pathname);
  const safePath = path.normalize(pathname).replace(/^([.][.][\/\\])+/, '');
  return path.join(root, safePath);
}

function sendNotFound(response) {
  response.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
  response.end('Not found');
}

const server = http.createServer((request, response) => {
  if (!['GET', 'HEAD'].includes(request.method)) {
    response.writeHead(405, { Allow: 'GET, HEAD' });
    response.end();
    return;
  }

  const filePath = resolveRequestPath(request.url || '/');
  if (!filePath.startsWith(root)) {
    sendNotFound(response);
    return;
  }

  fs.stat(filePath, (statError, stats) => {
    if (statError || !stats.isFile()) {
      sendNotFound(response);
      return;
    }

    const contentType = mimeTypes[path.extname(filePath).toLowerCase()] || 'application/octet-stream';
    response.writeHead(200, { 'Content-Type': contentType });
    if (request.method === 'HEAD') {
      response.end();
      return;
    }
    fs.createReadStream(filePath).pipe(response);
  });
});

server.listen(port, '127.0.0.1', () => {
  console.log(`Serving GenBI prototype at http://127.0.0.1:${port}`);
});
