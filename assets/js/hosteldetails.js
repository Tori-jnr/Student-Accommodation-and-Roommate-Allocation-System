const hostels = {
  greenview: {
    name: "Greenview Residence",
    location: "Madaraka, 0.5 km from campus",
    roomType: "Double room",
    availability: "Vacant",
    price: "KES 42,000 per month",
    landlord: "Greenview Properties",
    amenities: "Wi-Fi, study room, laundry, water storage",
    verificationDate: "15 June 2026",
    status: "Verified",
    hero: "linear-gradient(135deg, #1f2937, #164e63 55%, #99f6e4)",
    reviews: [
      ["4.5 stars - Clean and close to campus", "Security is reliable and rooms are quiet in the evening."],
      ["4 stars - Good transport access", "The caretaker responds quickly when there is an issue."]
    ]
  },
  magharibi: {
    name: "Magharibi Courts",
    location: "South B, near shopping mall",
    roomType: "Single room",
    availability: "Vacant",
    price: "KES 58,000 per month",
    landlord: "Magharibi Student Homes",
    amenities: "Wi-Fi, furnished room, water, CCTV, study lounge",
    verificationDate: "18 June 2026",
    status: "Verified",
    hero: "linear-gradient(135deg, #0f172a, #075985 55%, #38bdf8)",
    reviews: [
      ["5 stars - Very modern rooms", "The room is bright, secure, and close to transport."],
      ["4 stars - Good privacy", "Single rooms are comfortable, but demand is high."]
    ]
  },
  scholar: {
    name: "Scholar Heights",
    location: "Ongata Rongai, quiet study area",
    roomType: "Single room",
    availability: "Occupied",
    price: "KES 50,000 per month",
    landlord: "Scholar Heights Ltd",
    amenities: "Wi-Fi, shared kitchen, study desks, backup water",
    verificationDate: "12 June 2026",
    status: "Verified",
    hero: "linear-gradient(135deg, #111827, #14532d 55%, #86efac)",
    reviews: [
      ["4 stars - Best for budget", "Affordable and close enough to campus."],
      ["3.5 stars - Good but busy", "Shared spaces can be crowded during peak hours."]
    ]
  },

himalayan: {
    name: "Himalayan Hostel",
    location: "Lang'ata road, near campus link",
    roomType: "Premium Studio",
    availability: "Vacant",
    price: "KES 85,000 per month",
    landlord: "Himalayan Properties",
    amenities: "Wi-Fi, gym access, private bathroom, study area",
    verificationDate: "20 June 2026",
    status: "Verified",
    hero: "linear-gradient(135deg, #1e293b, #0f172a 55%, #64748b)",
    reviews: [
      ["5 stars - Luxurious and quiet", "The studio is spacious, well-furnished, and very secure."],
        ["4.5 stars - Great amenities", "Gym access is a big plus, and the location is convenient."]
    ]
    },

    sunrise: {
    name: "Sunrise Hostels",
    location: "Nairobi West, 1.2 km from campus",
    roomType: "Four shaaring",
    availability: "Vacant",
    price: "KES 18,000 per month",
    landlord: "Sunrise Properties",
    amenities: "Wi-Fi, laundry, study area",
    verificationDate: "22 June 2026",
    status: "Verified",
    hero: "linear-gradient(135deg, #1f2937, #164e63 55%, #99f6e4)",
    reviews: [
      ["4 stars - Good value for money", "The location is convenient, and the rooms are decent."],
      ["3.5 stars - Needs maintenance", "Some facilities are outdated and require attention."]
    ]
  },

  fivePoint: {
    name: "5 Point Residences",
    location: "South C, 3.5 km from campus",
    roomType: "Double Room",
    availability: "Vacant",
    price: "KES 38,000 per month",
    landlord: "5 Point Properties",
    amenities: "Wi-Fi, gym, study area",
    verificationDate: "25 June 2026",
    status: "Verified",
    hero: "linear-gradient(135deg, #1f2937, #164e63 55%, #99f6e4)",
    reviews: [
      ["4 stars - Modern and clean", "The facilities are well-maintained and the staff is friendly."],
      ["3.5 stars - Good location", "Close to campus, but traffic can be an issue."]
    ]
  },

  happy: {
    name: "Happy Living",
    location: "Kilimani, 1.0 km from campus",
    roomType: "Single Room",
    availability: "Vacant",
    price: "KES 70,000 per month",
    landlord: "Happy Living Ltd",
    amenities: "Wi-Fi, private bathroom, study area",
    verificationDate: "28 June 2026",
    status: "Verified",
    hero: "linear-gradient(135deg, #1f2937, #164e63 55%, #99f6e4)",
    reviews: [
      ["4.5 stars - Excellent facilities", "The rooms are spacious and the location is great."],
        ["4 stars - Good for quiet study", "The environment is peaceful, but the price is a bit high."]
    ]
   }

};

function setText(selector, value) {
  const element = document.querySelector(selector);
  if (element) element.textContent = value;
}

function loadHostelDetails() {
  const params = new URLSearchParams(window.location.search);
  const hostelId = params.get("id") || sessionStorage.getItem("selectedHostel") || "greenview";
  const hostel = hostels[hostelId] || hostels.greenview;

  sessionStorage.setItem("selectedHostel", hostelId);

  document.title = `${hostel.name} | Student Housing Portal`;
  document.documentElement.style.setProperty("--hero-image", hostel.hero);

  setText("[data-hostel-name]", hostel.name);
  setText("[data-hostel-location]", hostel.location);
  setText("[data-room-type]", hostel.roomType);
  setText("[data-availability]", hostel.availability);
  setText("[data-price]", hostel.price);
  setText("[data-status]", hostel.status);
  setText("[data-landlord]", hostel.landlord);
  setText("[data-amenities]", hostel.amenities);
  setText("[data-verification-date]", hostel.verificationDate);

  const tourLink = document.querySelector("[data-tour-link]");
  if (tourLink) tourLink.href = `virtual-tour.html?id=${hostelId}`;

  const contactLink = document.querySelector("[data-contact-link]");
  if (contactLink) contactLink.href = `contact-landlord.html?id=${hostelId}`;

  const reviews = document.querySelector("[data-review-list]");
  if (reviews) {
    reviews.innerHTML = hostel.reviews.map(function (review) {
      return `
        <div class="review-item">
          <strong>${review[0]}</strong>
          <span class="muted">${review[1]}</span>
        </div>
      `;
    }).join("");
  }
}

loadHostelDetails();
