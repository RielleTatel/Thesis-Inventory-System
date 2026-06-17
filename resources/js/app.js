

import Alpine from 'alpinejs';
import anchor from '@alpinejs/anchor';
import ocrScanner from './ocr-scanner';
import confirmModal from './confirm-modal';

window.Alpine = Alpine;

Alpine.plugin(anchor);
Alpine.data('ocrScanner', ocrScanner);
Alpine.data('confirmModal', confirmModal);
Alpine.start();
