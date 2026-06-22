// 1. Create a mini database of your tours
const toursDatabase = [
    {
        id: 1,
        name: "Greenview Residence Tour",
        type: "Double Room",
        panoramaUrl: "https://pannellum.org/images/alma.jpg" // Greenview image
    },
    {
        id: 2,
        name: "Magharibi Courts Tour",
        type: "Single Room",
        panoramaUrl: "https://pannellum.org/images/cerro-toco-0.jpg" // Karibu image
    },
    {
        id: 3,
        name: "Himalayan Hostel Tour",
        type: "Premium Studio",
        panoramaUrl: "https://pannellum.org/images/bma-0.jpg" 
    },

    {
        id: 4,
        name: "Sunrise Hostels Tour",
        type: "Four Sharing",
        panoramaUrl: "https://pannellum.org/images/alma.jpg" 
    },
    {
        id: 5,
        name: "Scholar Heights Tour",
        type: "Single Room",
        panoramaUrl: "https://cdn.polyhaven.com/asset_img/primary/relax_inn_seaview_suite.png?height=760&quality=95", // Scholar image
        hotspots: [
            {
                "pitch": 10,
                "yaw": 45,
                "type": "info",
                "text": "Click here to see the Kitchen!"
            }
        ]
            },
        
    
    {
        id: 6,
        name: "5 Point Residences Tour",
        type: "Double Room",
        panoramaUrl: "https://pannellum.org/images/bma-0.jpg" 
    },
    {
        id: 7,
        name: "Happy Living Tour",
        type: "Single Room",
        panoramaUrl: "https://pannellum.org/images/cerro-toco-0.jpg" // Karibu image
    }
];

document.addEventListener("DOMContentLoaded", () => {
    // 2. Look at the URL and grab the ID (e.g., virtualtour.html?id=2)
    const urlParams = new URLSearchParams(window.location.search);
    const hostelId = urlParams.get('id');
    
    // 3. Find the matching hostel in the database (Default to Greenview if no ID is found)
    const targetId = hostelId ? parseInt(hostelId) : 1;
    const currentTour = toursDatabase.find(h => h.id === targetId) || toursDatabase[0];

    // 4. Update the HTML text dynamically
    document.querySelector('.page-title h1').innerText = currentTour.name;
    document.querySelectorAll('.timeline-item .muted')[0].innerText = currentTour.name.replace(" Tour", "");
    document.querySelectorAll('.timeline-item .muted')[1].innerText = currentTour.type;

// Update Contact Link with the ID
    const contactBtn = document.querySelector('a[href="#"]');
    if (contactBtn) {
        contactBtn.href = `contact-landlord.html?id=${currentTour.id}`;
        contactBtn.classList.remove('disabled'); // Ensure it's active
    }


    // 5. Load the specific 3D picture into Pannellum!
    pannellum.viewer('panorama', {
        "type": "equirectangular",
        "panorama": currentTour.panoramaUrl, // <--- This changes the picture!
        "autoLoad": true
    });
});