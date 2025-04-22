import './bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';

import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

import './styles/app.css';

document.addEventListener('DOMContentLoaded', () => {
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    
    dropdownElementList.forEach(element => {
        new bootstrap.Dropdown(element);
    });
});