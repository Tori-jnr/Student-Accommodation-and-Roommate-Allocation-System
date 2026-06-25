// 1. Create a mini database of  tours
const toursDatabase = [
    {
        id: 1,
        name: "Greenview Residence Tour",
        type: "Double Room",
        panoramaUrl: "https://static.vecteezy.com/system/resources/thumbnails/012/054/071/small_2x/seamless-360-panorama-in-interior-of-bedroom-of-cheap-hostel-flat-or-apartments-with-chairs-and-table-in-equirectangular-projection-with-zenith-and-nadir-vr-ar-content-photo.jpg"
    },
    {
        id: 2,
        name: "Magharibi Courts Tour",
        type: "Single Room",
        panoramaUrl: "https://static.vecteezy.com/system/resources/thumbnails/017/167/738/small_2x/seamless-360-panorama-in-interior-of-bedroom-of-cheap-hostel-flat-or-apartments-with-chairs-and-table-in-equirectangular-projection-with-zenith-and-nadir-vr-ar-content-photo.jpg" 
    },
    {
        id: 3,
        name: "Himalayan Hostel Tour",
        type: "Premium Studio",
        panoramaUrl: "https://static.vecteezy.com/system/resources/thumbnails/022/594/867/small_2x/full-seamless-spherical-hdri-360-panorama-view-in-interior-of-modern-bedroom-in-vip-homestead-or-apaetments-with-panoramic-windows-in-equirectangular-projection-photo.jpg" 
    },

    {
        id: 4,
        name: "Sunrise Hostels Tour",
        type: "Four Sharing",
        panoramaUrl: "https://image.shutterstock.com/image-photo/hdri-360-panorama-interior-cheapest-260nw-2565572725.jpg" 

    },
    {
        id: 5,
        name: "Scholar Heights Tour",
        type: "Single Room",
        panoramaUrl: "https://cdn.polyhaven.com/asset_img/primary/relax_inn_seaview_suite.png?height=760&quality=95"
 },
        
    
    {
        id: 6,
        name: "5 Point Residences Tour",
        type: "Double Room",
        panoramaUrl: "https://static.vecteezy.com/system/resources/thumbnails/022/796/926/small_2x/full-360-hdri-panorama-in-interior-of-wooden-eco-bedroom-in-rustic-style-hostel-or-homestead-on-mansard-floor-in-equirectangular-projection-with-zenith-and-nadir-vr-ar-content-photo.jpg"
    },
    {
        id: 7,
        name: "Happy Living Tour",
        type: "Single Room",
        panoramaUrl: "https://static.vecteezy.com/system/resources/thumbnails/012/054/084/small_2x/seamless-360-panorama-in-interior-of-bedroom-of-cheap-hostel-flat-or-apartments-with-chairs-and-table-in-equirectangular-projection-with-zenith-and-nadir-vr-ar-content-photo.jpg" 
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
        "autoLoad": true,
        "autoRotate": -2,
       "hfov": 110,

    });
});