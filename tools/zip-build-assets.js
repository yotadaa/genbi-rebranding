#!/usr/bin/env node

const fs = require('node:fs');
const path = require('node:path');
const zlib = require('node:zlib');
const { spawnSync } = require('node:child_process');

const rootDir = path.resolve(__dirname, '..');
const zipPath = path.resolve(rootDir, process.argv[2] || 'public/assets-build.zip');
const npmCommand = 'npm';

const zipEntries = [
  'public/assets/css/tailwind.css',
  'public/assets/css/styles.min.css',
  'public/assets/css/theme.css',
  'public/assets/js/dist',
];

function run(label, command, args) {
  console.log(label);
  const isWindows = process.platform === 'win32';
  const result = isWindows ? spawnSync('cmd.exe', ['/d', '/s', '/c', [command, ...args].join(' ')], {
    cwd: rootDir,
    stdio: 'inherit',
  }) : spawnSync(command, args, {
    cwd: rootDir,
    shell: false,
    stdio: 'inherit',
  });

  if (result.error) {
    throw result.error;
  }

  if (result.status !== 0) {
    process.exit(result.status || 1);
  }
}

function collectFiles(entryPath) {
  const absolute = path.join(rootDir, entryPath);
  if (!fs.existsSync(absolute)) {
    throw new Error(`Missing build asset: ${entryPath}`);
  }

  const stat = fs.statSync(absolute);
  if (stat.isFile()) {
    return [absolute];
  }

  if (!stat.isDirectory()) {
    return [];
  }

  const files = [];
  const stack = [absolute];
  while (stack.length > 0) {
    const directory = stack.pop();
    for (const child of fs.readdirSync(directory, { withFileTypes: true })) {
      const childPath = path.join(directory, child.name);
      if (child.isDirectory()) {
        stack.push(childPath);
      } else if (child.isFile()) {
        files.push(childPath);
      }
    }
  }

  return files.sort((a, b) => zipName(a).localeCompare(zipName(b)));
}

function zipName(absolutePath) {
  return path.relative(rootDir, absolutePath).split(path.sep).join('/');
}

const crcTable = new Uint32Array(256);
for (let i = 0; i < crcTable.length; i += 1) {
  let value = i;
  for (let bit = 0; bit < 8; bit += 1) {
    value = value & 1 ? 0xedb88320 ^ (value >>> 1) : value >>> 1;
  }
  crcTable[i] = value >>> 0;
}

function crc32(buffer) {
  let crc = 0xffffffff;
  for (const byte of buffer) {
    crc = crcTable[(crc ^ byte) & 0xff] ^ (crc >>> 8);
  }
  return (crc ^ 0xffffffff) >>> 0;
}

function dosDateTime(date) {
  const year = Math.max(1980, Math.min(2107, date.getFullYear()));
  const month = date.getMonth() + 1;
  const day = date.getDate();
  const hours = date.getHours();
  const minutes = date.getMinutes();
  const seconds = Math.floor(date.getSeconds() / 2);

  return {
    date: ((year - 1980) << 9) | (month << 5) | day,
    time: (hours << 11) | (minutes << 5) | seconds,
  };
}

function writeZip(outputPath, files) {
  const chunks = [];
  const centralDirectory = [];
  let offset = 0;

  for (const file of files) {
    const name = zipName(file);
    const nameBuffer = Buffer.from(name, 'utf8');
    const data = fs.readFileSync(file);
    const compressed = zlib.deflateRawSync(data, { level: 9 });
    const stat = fs.statSync(file);
    const timestamp = dosDateTime(stat.mtime);
    const crc = crc32(data);

    const localHeader = Buffer.alloc(30);
    localHeader.writeUInt32LE(0x04034b50, 0);
    localHeader.writeUInt16LE(20, 4);
    localHeader.writeUInt16LE(0, 6);
    localHeader.writeUInt16LE(8, 8);
    localHeader.writeUInt16LE(timestamp.time, 10);
    localHeader.writeUInt16LE(timestamp.date, 12);
    localHeader.writeUInt32LE(crc, 14);
    localHeader.writeUInt32LE(compressed.length, 18);
    localHeader.writeUInt32LE(data.length, 22);
    localHeader.writeUInt16LE(nameBuffer.length, 26);
    localHeader.writeUInt16LE(0, 28);

    chunks.push(localHeader, nameBuffer, compressed);

    const centralHeader = Buffer.alloc(46);
    centralHeader.writeUInt32LE(0x02014b50, 0);
    centralHeader.writeUInt16LE(20, 4);
    centralHeader.writeUInt16LE(20, 6);
    centralHeader.writeUInt16LE(0, 8);
    centralHeader.writeUInt16LE(8, 10);
    centralHeader.writeUInt16LE(timestamp.time, 12);
    centralHeader.writeUInt16LE(timestamp.date, 14);
    centralHeader.writeUInt32LE(crc, 16);
    centralHeader.writeUInt32LE(compressed.length, 20);
    centralHeader.writeUInt32LE(data.length, 24);
    centralHeader.writeUInt16LE(nameBuffer.length, 28);
    centralHeader.writeUInt16LE(0, 30);
    centralHeader.writeUInt16LE(0, 32);
    centralHeader.writeUInt16LE(0, 34);
    centralHeader.writeUInt16LE(0, 36);
    centralHeader.writeUInt32LE(0, 38);
    centralHeader.writeUInt32LE(offset, 42);
    centralDirectory.push(centralHeader, nameBuffer);

    offset += localHeader.length + nameBuffer.length + compressed.length;
  }

  const centralDirectoryBuffer = Buffer.concat(centralDirectory);
  const endRecord = Buffer.alloc(22);
  endRecord.writeUInt32LE(0x06054b50, 0);
  endRecord.writeUInt16LE(0, 4);
  endRecord.writeUInt16LE(0, 6);
  endRecord.writeUInt16LE(files.length, 8);
  endRecord.writeUInt16LE(files.length, 10);
  endRecord.writeUInt32LE(centralDirectoryBuffer.length, 12);
  endRecord.writeUInt32LE(offset, 16);
  endRecord.writeUInt16LE(0, 20);

  fs.mkdirSync(path.dirname(outputPath), { recursive: true });
  fs.writeFileSync(outputPath, Buffer.concat([...chunks, centralDirectoryBuffer, endRecord]));
}

try {
  run('Building Tailwind CSS...', npmCommand, ['run', 'build:css']);
  run('Building theme CSS...', npmCommand, ['run', 'build:themes']);
  run('Building JS dist and minified site CSS...', npmCommand, ['run', 'build:js']);

  console.log(`Creating build asset zip: ${path.relative(rootDir, zipPath)}`);
  if (fs.existsSync(zipPath)) {
    fs.rmSync(zipPath, { force: true });
  }
  const files = Array.from(new Set(zipEntries.flatMap(collectFiles))).sort((a, b) => zipName(a).localeCompare(zipName(b)));
  writeZip(zipPath, files);
  console.log(`Done: ${path.relative(rootDir, zipPath)}`);
} catch (error) {
  console.error(error instanceof Error ? error.message : error);
  process.exit(1);
}
