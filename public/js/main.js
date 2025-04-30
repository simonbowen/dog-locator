const locateButton = document.getElementById('locate');

let coords;
let marker;

const mapContainer = document.getElementById('map');
const map = L.map('map');
const modal = document.getElementById('modal-form');
const modalSuccess = document.getElementById('modal-success');
const closeButton = document.getElementById('cancel');
const submitButton = document.getElementById('submit');
const closeSuccessButton = document.getElementById('close-success');

closeButton.addEventListener('click', (e) => {
    e.preventDefault();
    modal.classList.add('hidden');
});

submitButton.addEventListener('click', async (e) => {
    const form = new FormData();
    form.append('latitude', coords.latitude);
    form.append('longitude', coords.longitude);
    form.append('message', document.getElementById('message').value);

    await fetch('/', {
        method: 'POST',
        body: form
    });

    modal.classList.add('hidden');
    modalSuccess.classList.remove('hidden');
});

closeSuccessButton.addEventListener('click', (e) => {
    e.preventDefault();
    modalSuccess.classList.add('hidden');
});

const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
}).addTo(map);

function loadMap() { 
    modal.classList.remove('hidden');

    if (marker) {
        map.removeLayer(marker);
    }
    
    map.setView([coords.latitude, coords.longitude], 13);
    marker = L.marker([coords.latitude, coords.longitude]).addTo(map);
    map.invalidateSize();
}

function locateHandler(e) {
    navigator.geolocation.getCurrentPosition((pos) => {
        coords = pos.coords;
        loadMap();
    });

    e.preventDefault();
}

locateButton.addEventListener('click', locateHandler);