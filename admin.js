// PG Management Functions
let currentPgId = null;
let pgs = []; // Store all PGs

function openModal(pgId = null) {
    const modal = document.getElementById('pgModal');
    const modalTitle = document.getElementById('modalTitle');
    currentPgId = pgId;
    
    if (pgId) {
        modalTitle.textContent = 'Edit PG';
        // Load existing PG data
        loadPgData(pgId);
    } else {
        modalTitle.textContent = 'Add New PG';
        resetForm();
    }
    
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('pgModal');
    modal.style.display = 'none';
    currentPgId = null;
    resetForm();
}

function resetForm() {
    document.getElementById('pgForm').reset();
    const roomTypesContainer = document.getElementById('roomTypes');
    roomTypesContainer.innerHTML = '';
    addRoomType(); // Add one default room type
}

function loadPgData(pgId) {
    const pg = pgs.find(p => p.id === pgId);
    if (!pg) return;

    document.getElementById('pgName').value = pg.name;
    document.getElementById('location').value = pg.location;
    document.getElementById('price').value = pg.price;
    document.getElementById('description').value = pg.description;

    // Set amenities checkboxes
    document.querySelectorAll('input[name="amenities"]').forEach(checkbox => {
        checkbox.checked = pg.amenities.includes(checkbox.value);
    });

    // Add room types
    const roomTypesContainer = document.getElementById('roomTypes');
    roomTypesContainer.innerHTML = '';
    pg.roomTypes.forEach(room => {
        addRoomType(room.type, room.price, room.available);
    });

    // Load images
    const imagePreview = document.getElementById('imagePreview');
    imagePreview.innerHTML = '';
    if (pg.images && pg.images.length > 0) {
        pg.images.forEach(image => {
            const previewImage = document.createElement('div');
            previewImage.className = 'preview-image';
            previewImage.innerHTML = `
                <img src="${image}" alt="Preview">
                <div class="remove-image" onclick="removeImage(this)">
                    <i class='bx bx-x'></i>
                </div>
            `;
            imagePreview.appendChild(previewImage);
        });
    } else {
        imagePreview.innerHTML = `
            <div class="upload-placeholder">
                <i class='bx bx-image-add'></i>
                <span>Click to upload images</span>
            </div>
        `;
    }
}

function addRoomType(type = '', price = '', available = '') {
    const roomTypesContainer = document.getElementById('roomTypes');
    const roomTypeDiv = document.createElement('div');
    roomTypeDiv.className = 'room-type';
    roomTypeDiv.innerHTML = `
        <input type="text" placeholder="Room Type" value="${type}" required>
        <input type="number" placeholder="Price" value="${price}" required>
        <input type="number" placeholder="Available" value="${available}" required>
    `;
    roomTypesContainer.appendChild(roomTypeDiv);
}

function handlePgFormSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const pgData = {
        id: currentPgId || Date.now(), // Use existing ID or generate new one
        name: formData.get('pgName'),
        location: formData.get('location'),
        price: formData.get('price'),
        description: formData.get('description'),
        amenities: Array.from(formData.getAll('amenities')),
        roomTypes: [],
        images: [],
        status: 'available'
    };

    // Collect room type data
    const roomTypes = document.querySelectorAll('.room-type');
    roomTypes.forEach(room => {
        const inputs = room.querySelectorAll('input');
        pgData.roomTypes.push({
            type: inputs[0].value,
            price: inputs[1].value,
            available: inputs[2].value
        });
    });

    // Collect image data
    const imagePreview = document.getElementById('imagePreview');
    const images = imagePreview.querySelectorAll('.preview-image img');
    images.forEach(img => {
        pgData.images.push(img.src);
    });

    // Update or add PG
    if (currentPgId) {
        const index = pgs.findIndex(p => p.id === currentPgId);
        if (index !== -1) {
            pgs[index] = pgData;
        }
    } else {
        pgs.push(pgData);
    }

    // Save to localStorage (in a real app, this would be an API call)
    localStorage.setItem('pgs', JSON.stringify(pgs));
    
    showMessage('PG saved successfully!', 'success');
    closeModal();
    loadPgList();
}

// Image Upload Functions
function handleImageUpload(event) {
    const files = event.target.files;
    const imagePreview = document.getElementById('imagePreview');
    imagePreview.innerHTML = '';

    if (files.length === 0) {
        imagePreview.innerHTML = `
            <div class="upload-placeholder">
                <i class='bx bx-image-add'></i>
                <span>Click to upload images</span>
            </div>
        `;
        return;
    }

    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImage = document.createElement('div');
                previewImage.className = 'preview-image';
                previewImage.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <div class="remove-image" onclick="removeImage(this)">
                        <i class='bx bx-x'></i>
                    </div>
                `;
                imagePreview.appendChild(previewImage);
            };
            reader.readAsDataURL(file);
        }
    });
}

function removeImage(element) {
    element.parentElement.remove();
    const imagePreview = document.getElementById('imagePreview');
    if (imagePreview.children.length === 0) {
        imagePreview.innerHTML = `
            <div class="upload-placeholder">
                <i class='bx bx-image-add'></i>
                <span>Click to upload images</span>
            </div>
        `;
    }
}

// Search Functionality
function handleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const pgCards = document.querySelectorAll('.pg-card');
    
    pgCards.forEach(card => {
        const pgName = card.querySelector('h3').textContent.toLowerCase();
        const pgLocation = card.querySelector('.location span').textContent.toLowerCase();
        const pgAmenities = Array.from(card.querySelectorAll('.amenities span')).map(span => span.textContent.toLowerCase());
        
        const matches = pgName.includes(searchTerm) || 
                       pgLocation.includes(searchTerm) || 
                       pgAmenities.some(amenity => amenity.includes(searchTerm));
        
        card.style.display = matches ? 'block' : 'none';
    });
}

// Statistics Functions
function updateStatistics(pgs) {
    let totalRooms = 0;
    let availableRooms = 0;
    let totalTenants = 0;
    let monthlyRevenue = 0;

    pgs.forEach(pg => {
        pg.roomTypes.forEach(room => {
            totalRooms += parseInt(room.available);
            availableRooms += parseInt(room.available);
            totalTenants += parseInt(room.available) - parseInt(room.available);
            monthlyRevenue += parseInt(room.price) * (parseInt(room.available) - parseInt(room.available));
        });
    });

    document.getElementById('totalPgs').textContent = pgs.length;
    document.getElementById('availableRooms').textContent = availableRooms;
    document.getElementById('totalTenants').textContent = totalTenants;
    document.getElementById('monthlyRevenue').textContent = `₹${monthlyRevenue.toLocaleString()}`;
}

// Update loadPgList function
function loadPgList() {
    // Load PGs from localStorage (in a real app, this would be an API call)
    const savedPgs = localStorage.getItem('pgs');
    if (savedPgs) {
        pgs = JSON.parse(savedPgs);
    }

    const pgList = document.getElementById('pgList');
    pgList.innerHTML = '';

    if (pgs.length === 0) {
        pgList.innerHTML = `
            <div class="no-pgs">
                <i class='bx bx-building-house'></i>
                <h3>No PGs Added Yet</h3>
                <p>Click the "Add New PG" button to add your first PG.</p>
            </div>
        `;
        return;
    }

    pgs.forEach(pg => {
        const pgCard = document.createElement('div');
        pgCard.className = 'pg-card';
        pgCard.innerHTML = `
            <div class="pg-image">
                <img src="${pg.images && pg.images.length > 0 ? pg.images[0] : 'images/pg-placeholder.jpg'}" alt="${pg.name}">
                <span class="pg-status ${pg.status}">${pg.status}</span>
            </div>
            <div class="pg-info">
                <h3>${pg.name}</h3>
                <div class="location">
                    <i class='bx bx-map'></i>
                    <span>${pg.location}</span>
                </div>
                <div class="price">
                    <i class='bx bx-rupee'></i>
                    <span>${pg.price}/month</span>
                </div>
                <div class="room-types">
                    ${pg.roomTypes.map(room => `
                        <div class="room-type-info">
                            <span>${room.type}</span>
                            <span>₹${room.price}</span>
                            <span>${room.available} available</span>
                        </div>
                    `).join('')}
                </div>
                <div class="amenities">
                    ${pg.amenities.map(amenity => `
                        <span>
                            <i class='bx bx-check'></i>
                            ${amenity}
                        </span>
                    `).join('')}
                </div>
                <div class="pg-actions">
                    <button class="edit-btn" onclick="openModal(${pg.id})">
                        <i class='bx bx-edit'></i>
                        Edit
                    </button>
                    <button class="delete-btn" onclick="deletePg(${pg.id})">
                        <i class='bx bx-trash'></i>
                        Delete
                    </button>
                </div>
            </div>
        `;
        pgList.appendChild(pgCard);
    });

    updateStatistics(pgs);
}

function deletePg(pgId) {
    if (confirm('Are you sure you want to delete this PG? This action cannot be undone.')) {
        pgs = pgs.filter(pg => pg.id !== pgId);
        localStorage.setItem('pgs', JSON.stringify(pgs));
        showMessage('PG deleted successfully!', 'success');
        loadPgList();
    }
}

// Update initialization
document.addEventListener('DOMContentLoaded', () => {
    const addPgBtn = document.getElementById('addPgBtn');
    const closeModalBtn = document.getElementById('closeModal');
    const pgForm = document.getElementById('pgForm');
    const addRoomBtn = document.getElementById('addRoomBtn');
    const pgImages = document.getElementById('pgImages');
    const pgSearch = document.getElementById('pgSearch');

    if (addPgBtn) addPgBtn.addEventListener('click', () => openModal());
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (pgForm) pgForm.addEventListener('submit', handlePgFormSubmit);
    if (addRoomBtn) addRoomBtn.addEventListener('click', addRoomType);
    if (pgImages) pgImages.addEventListener('change', handleImageUpload);
    if (pgSearch) pgSearch.addEventListener('input', handleSearch);

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        const modal = document.getElementById('pgModal');
        if (event.target === modal) {
            closeModal();
        }
    });

    // Load initial PG list
    loadPgList();
}); 