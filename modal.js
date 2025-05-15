// Debug function to help us see what's happening
function debug(message) {
    console.log(`Debug: ${message}`);
}

// Simple modal functions
function showPgDetails(pgId) {
    const modal = document.getElementById(`pgDetailsModal-${pgId}`);
    modal.style.display = 'block';
}

function changeMainImage(pgId, src) {
    const modalImage = document.getElementById(`modalPgImage-${pgId}`);
    const thumbnails = document.querySelectorAll(`#pgDetailsModal-${pgId} .image-thumbnails img`);
    modalImage.src = src;
}

function changeCardImage(pgId, direction) {
    const images = ['img/pg 1.webp', 'img/pg 2.webp', 'img/pg 3.webp', 'img/pg 4.webp'];
    const imgElement = document.querySelector(`#pg-box-${pgId} .slide-image`);
    let currentIndex = images.indexOf(imgElement.src.split('/').pop());
    if (currentIndex === -1) currentIndex = 0;
    currentIndex = (currentIndex + direction + images.length) % images.length;
    imgElement.src = images[currentIndex];
}

// Close button functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add click events to all close buttons
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.style.display = 'none';
        });
    });

    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
});

function openBookingModal(pgName) {
    alert(`Opening booking form for ${pgName}`);
}

function openEnquiryModal(pgName) {
    alert(`Opening enquiry form for ${pgName}`);
}

// Add keyboard support for closing modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal[style*="display: block"]');
        if (activeModal) {
            const pgId = activeModal.id.replace('pgDetailsModal-', '');
            document.getElementById(`pgDetailsModal-${pgId}`).style.display = 'none';
        }
    }
}); 