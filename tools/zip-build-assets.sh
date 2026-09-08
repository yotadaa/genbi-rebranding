#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

ZIP_PATH="${1:-public/assets-build.zip}"

if ! command -v zip >/dev/null 2>&1; then
  echo "Error: zip command not found. Install zip first, then rerun this script." >&2
  exit 1
fi

echo "Building Tailwind CSS..."
npm run build:css

echo "Building theme CSS..."
npm run build:themes

echo "Building JS dist and minified site CSS..."
npm run build:js

echo "Creating build asset zip: $ZIP_PATH"
rm -f "$ZIP_PATH"
zip -r "$ZIP_PATH" \
  public/assets/css/tailwind.css \
  public/assets/css/styles.min.css \
  public/assets/css/theme.css \
  public/assets/js/dist

echo "Done: $ZIP_PATH"
