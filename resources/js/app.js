import './bootstrap';

import Alpine from 'alpinejs';

import axios from 'axios';

window.Alpine = Alpine;

Alpine.start();

const login = async (email, password) => {
    try {
        const response = await axios.post('/api/auth/login', { email, password });
        // Salva il token e gestisci la sessione
        localStorage.setItem('token', response.data.token);
        // Reindirizza o aggiorna lo stato dell'app
    } catch (error) {
        console.error('Login failed:', error.response.data.message);
    }
};
