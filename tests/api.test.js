const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

test('submitNewsComment preserves CSRF and Accept headers for JSON POST requests', async () => {
  const apiScript = fs.readFileSync(path.join(__dirname, '..', 'public', 'assets', 'js', 'api.js'), 'utf8');
  const fetchCalls = [];
  const context = {
    document: {
      querySelector(selector) {
        if (selector === 'meta[name="csrf-token"]') {
          return {
            getAttribute(name) {
              return name === 'content' ? 'csrf-123' : null;
            },
          };
        }
        return null;
      },
    },
    window: {
      location: { protocol: 'http:' },
      GenBIData: {},
      GenBIAPICore: {
        canRequestBackend: () => true,
        createCommentPayload: (payload) => ({ ...payload }),
        routeUrl: (name, params) => `/news/${params.slug}/comment`,
      },
      fetch: async (url, options) => {
        fetchCalls.push({ url, options });
        return {
          ok: true,
          async json() {
            return { ok: true };
          },
        };
      },
    },
  };
  context.window.window = context.window;
  context.window.document = context.document;

  vm.runInNewContext(apiScript, context, { filename: 'api.js' });

  await context.window.GenBIAPI.submitNewsComment(
    { slug: 'talkshow-siginjai-fest-2026-dorong-generasi-muda-berkarya' },
    { name: 'Rina', email: 'rina@example.com', comment: 'Halo semua' }
  );

  assert.equal(fetchCalls.length, 1);
  assert.equal(fetchCalls[0].url, '/news/talkshow-siginjai-fest-2026-dorong-generasi-muda-berkarya/comment');
  assert.equal(fetchCalls[0].options.credentials, 'same-origin');
  assert.equal(fetchCalls[0].options.headers.Accept, 'application/json');
  assert.equal(fetchCalls[0].options.headers['Content-Type'], 'application/json');
  assert.equal(fetchCalls[0].options.headers['X-CSRF-TOKEN'], 'csrf-123');

  const parsedBody = JSON.parse(fetchCalls[0].options.body);
  assert.equal(parsedBody._csrf_token, 'csrf-123');
  assert.equal(parsedBody.name, 'Rina');
  assert.equal(parsedBody.email, 'rina@example.com');
  assert.equal(parsedBody.comment, 'Halo semua');
});
