const fs = require('fs');
const path = require('path');

const root = 'e:/work/Wnmp/html/tennisplay';

const viewBase = path.join(root, 'resources', 'views');

function resolveInclude(includePath) {
  const normalized = includePath.replace(/\./g, path.sep) + '.blade.php';
  return path.join(viewBase, normalized);
}

function expandIncludes(filePath, seen = new Set()) {
  if (seen.has(filePath)) return '';
  seen.add(filePath);
  let content = fs.readFileSync(filePath, 'utf8');
  const includeRegex = /@include\(['"]([^'"]+)['"]\)/g;
  content = content.replace(includeRegex, (_, includePath) => {
    const includeFile = resolveInclude(includePath);
    if (!fs.existsSync(includeFile)) {
      return `\n/* Missing include: ${includePath} */\n`;
    }
    return '\n' + expandIncludes(includeFile, seen) + '\n';
  });
  return content;
}

const entry = path.join(viewBase, 'partials', 'vue-scripts.blade.php');
const expanded = expandIncludes(entry);

// Extract JS from the script tag
const scriptMatch = expanded.match(/<script>\s*([\s\S]*?)\s*<\/script>/i);
if (!scriptMatch) {
  console.error('No <script> block found in vue-scripts.blade.php');
  process.exit(1);
}

const code = scriptMatch[1];
try {
  new Function(code);
  console.log('No syntax errors in expanded Vue script.');
} catch (error) {
  const lines = code.split(/\r?\n/);
  const line = error.lineNumber || error.line || 0;
  const col = error.columnNumber || error.column || 0;
  console.error(`Syntax error: ${error.message}`);
  console.error(`Line ${line}, Col ${col}`);
  const start = Math.max(0, line - 3);
  const end = Math.min(lines.length, line + 2);
  console.error(lines.slice(start, end).join('\n'));
  process.exit(1);
}
