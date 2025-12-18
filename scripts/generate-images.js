const fs = require('fs');
const path = require('path');

const projectRoot = path.join(__dirname, '..');
const imagesDir = path.join(projectRoot, 'images', 'slider');
const outFile = path.join(projectRoot, 'images.json');

if (!fs.existsSync(imagesDir)) {
  console.error('images directory not found:', imagesDir);
  process.exit(1);
}

const files = fs.readdirSync(imagesDir).filter(f => {
  if (f === '.' || f === '..') return false;
  const ext = path.extname(f).toLowerCase();
  return ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg'].includes(ext);
});

files.sort((a,b) => a.localeCompare(b, undefined, {numeric:true, sensitivity:'base'}));
const urls = files.map(f => 'images/slider/' + f);

fs.writeFileSync(outFile, JSON.stringify(urls, null, 2), 'utf8');
console.log('Wrote', outFile, 'with', urls.length, 'entries');
