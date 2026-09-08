const { readdirSync, statSync, mkdirSync } = require('node:fs')
const { join, relative, dirname } = require('node:path')
const { buildSync } = require('esbuild')

const srcDir = join(__dirname, '..', 'public', 'assets', 'js')
const outDir = join(__dirname, '..', 'public', 'assets', 'js', 'dist')

function collectJs(dir) {
  const files = []
  for (const entry of readdirSync(dir)) {
    if (entry === 'dist') continue
    const full = join(dir, entry)
    if (statSync(full).isDirectory()) {
      files.push(...collectJs(full))
    } else if (entry.endsWith('.js')) {
      files.push(full)
    }
  }
  return files
}

const entries = collectJs(srcDir)

for (const file of entries) {
  const rel = relative(srcDir, file)
  const out = join(outDir, rel)
  mkdirSync(dirname(out), { recursive: true })
  buildSync({ entryPoints: [file], outfile: out, minify: true, platform: 'browser' })
}

// Minify styles.css
const cssIn = join(__dirname, '..', 'public', 'assets', 'css', 'styles.css')
const cssOut = join(__dirname, '..', 'public', 'assets', 'css', 'styles.min.css')
buildSync({ entryPoints: [cssIn], outfile: cssOut, minify: true })

console.log(`Minified ${entries.length} JS files to public/assets/js/dist/`)
console.log('Minified styles.css → styles.min.css')
