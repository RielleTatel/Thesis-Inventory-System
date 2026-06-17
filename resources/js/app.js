

import Alpine from 'alpinejs';
import anchor from '@alpinejs/anchor';
import ocrScanner from './ocr-scanner';

window.Alpine = Alpine;

Alpine.plugin(anchor);
Alpine.data('ocrScanner', ocrScanner);
Alpine.start();
