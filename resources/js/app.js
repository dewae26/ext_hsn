import * as bootstrap from 'bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import { renderPdf, clearPdf } from './pdf-viewer';

window.bootstrap = bootstrap;
window.Alpine = Alpine;
window.Chart = Chart;
window.$ = window.jQuery = $;
window.DataTable = DataTable;
window.HvPdf = { render: renderPdf, clear: clearPdf };

Alpine.start();
