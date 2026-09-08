const fs = require('node:fs')
const path = require('node:path')

const registryPath = path.join(__dirname, '..', 'app', 'Config', 'ThemeRegistry.php')
const source = fs.readFileSync(registryPath, 'utf8')

const pairs = [...source.matchAll(/'([a-z0-9-]+)' => \['name' => '([^']+)', 'mode' => '([^']+)'/g)]
if (!pairs.length) {
  throw new Error('Could not detect themes from ThemeRegistry.php')
}

const themeCssPath = path.join(__dirname, '..', 'public', 'assets', 'css', 'theme.css')
const css = fs.readFileSync(themeCssPath, 'utf8')

for (const [, key, name] of pairs) {
  const block = key === 'genbi'
    ? css.match(/:root,[\s\S]*?html\[data-theme="genbi"\] \{([\s\S]*?)\n\}/)
    : css.match(new RegExp(`html\\[data-theme=\\"${key}\\"\\] \\{([\\s\\S]*?)\\n\\}`))

  if (!block) {
    throw new Error(`Missing theme block for ${key}`)
  }

  const tokens = Object.fromEntries([...block[1].matchAll(/--([a-z0-9-]+):\s*([^;]+);/g)].map((match) => [match[1], match[2].trim()]))
  const text = rgbFromChannels(tokens['text-primary'])
  const bg = rgbFromChannels(tokens['background-default'])
  const brand = rgbFromChannels(tokens['brand-primary'])

  const bodyRatio = contrast(text, bg)
  const brandRatio = contrast(brand, bg)

  if (bodyRatio < 4.5) {
    throw new Error(`${key} (${name}) body contrast too low: ${bodyRatio.toFixed(2)}`)
  }
  if (brandRatio < 3) {
    throw new Error(`${key} (${name}) brand contrast too low: ${brandRatio.toFixed(2)}`)
  }
}

console.log('Theme contrast checks passed')

function rgbFromChannels(value) {
  const [r, g, b] = String(value).split(/\s+/).slice(0, 3).map(Number)
  return [r, g, b]
}

function contrast(foreground, background) {
  const l1 = luminance(foreground)
  const l2 = luminance(background)
  const lighter = Math.max(l1, l2)
  const darker = Math.min(l1, l2)
  return (lighter + 0.05) / (darker + 0.05)
}

function luminance([r, g, b]) {
  const values = [r, g, b].map((channel) => {
    const normalized = channel / 255
    return normalized <= 0.03928 ? normalized / 12.92 : ((normalized + 0.055) / 1.055) ** 2.4
  })
  return (0.2126 * values[0]) + (0.7152 * values[1]) + (0.0722 * values[2])
}
