const fs = require('fs');

const htmlPath = 'e:/work/Wnmp/html/tennisplay/full_output.html';
const html = fs.readFileSync(htmlPath, 'utf8');
const scriptRegex = /<script\b(?![^>]*type=['"]application\/ld\+json['"])\b[^>]*>([\s\S]*?)<\/script>/gi;

let match;
let index = 0;
let failed = false;

while ((match = scriptRegex.exec(html))) {
  index += 1;
  const code = match[1].trim();
  if (!code) continue;
  try {
    // Parse only
    new Function(code);
  } catch (error) {
    failed = true;
    const lines = code.split(/\r?\n/);
    const line = error.lineNumber || error.line || 0;
    const col = error.columnNumber || error.column || 0;
    console.error(`Script ${index} syntax error: ${error.message}`);
    console.error(`Line ${line}, Col ${col}`);
    const start = Math.max(0, line - 3);
    const end = Math.min(lines.length, line + 2);
    console.error(lines.slice(start, end).join('\n'));
  }
}

if (!failed) {
  console.log('No syntax errors in extracted scripts.');
}
