// Leaflet Maps with Geocoder Integration
class LeafletMaps {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.map = null;
        this.marker = null;
        this.geocoder = null;
        this.defaultCenter = options.defaultCenter || [-6.2, 106.816666]; // Jakarta
        this.defaultZoom = options.defaultZoom || 13;
        this.latitudeInput = options.latitudeInput || "latitude";
        this.longitudeInput = options.longitudeInput || "longitude";
        this.locationInput = options.locationInput || "location";
        this.addressInput = options.addressInput || "address";
        this.isResidence = options.isResidence || false;

        this.init();
    }

    init() {
        this.initMap();
        this.initGeocoder();
        this.initEventListeners();
        this.loadExistingLocation();
    }

    initMap() {
        // Initialize map
        this.map = L.map(this.containerId).setView(
            this.defaultCenter,
            this.defaultZoom
        );

        // Add OpenStreetMap tiles
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
        }).addTo(this.map);

        // Add click event to map
        this.map.on("click", (e) => {
            this.setMarker(e.latlng);
            this.updateCoordinates(e.latlng.lat, e.latlng.lng);
            this.reverseGeocode(e.latlng.lat, e.latlng.lng);
        });
    }

    initGeocoder() {
        // Initialize geocoder
        this.geocoder = L.Control.Geocoder.nominatim({
            geocodingQueryParams: {
                countrycodes: "id", // Limit to Indonesia
                limit: 5,
            },
        });

        // Add geocoder control to map
        this.geocoder.addTo(this.map);

        // Handle geocoder results
        this.geocoder.on("markgeocode", (e) => {
            const result = e.geocode;
            this.setMarker(result.center);
            this.updateCoordinates(result.center.lat, result.center.lng);
            this.updateLocationInput(result.name);
        });
    }

    initEventListeners() {
        // Listen for input changes
        const latInput = document.getElementById(this.latitudeInput);
        const lngInput = document.getElementById(this.longitudeInput);
        const locationInput = document.getElementById(this.locationInput);
        const addressInput = document.getElementById(this.addressInput);

        if (latInput && lngInput) {
            latInput.addEventListener("change", () => {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    this.setMarker([lat, lng]);
                    this.map.setView([lat, lng], this.map.getZoom());
                }
            });

            lngInput.addEventListener("change", () => {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    this.setMarker([lat, lng]);
                    this.map.setView([lat, lng], this.map.getZoom());
                }
            });
        }

        // Add search functionality to location/address input
        if (locationInput) {
            this.addSearchToInput(locationInput);
        }
        if (addressInput) {
            this.addSearchToInput(addressInput);
        }
    }

    addSearchToInput(input) {
        let searchTimeout;

        input.addEventListener("input", (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            if (query.length > 3) {
                searchTimeout = setTimeout(() => {
                    this.searchLocation(query);
                }, 500);
            }
        });
    }

    searchLocation(query) {
        // Use Nominatim API for search
        fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
                query
            )}&countrycodes=id&limit=5`
        )
            .then((response) => response.json())
            .then((data) => {
                if (data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);

                    this.setMarker([lat, lng]);
                    this.updateCoordinates(lat, lng);
                    this.map.setView([lat, lng], 15);
                }
            })
            .catch((error) => {
                console.error("Geocoding error:", error);
            });
    }

    setMarker(latlng) {
        // Remove existing marker
        if (this.marker) {
            this.map.removeLayer(this.marker);
        }

        // Add new marker
        this.marker = L.marker(latlng).addTo(this.map);

        // Add popup
        this.marker
            .bindPopup(
                `
            <div>
                <strong>Lokasi Terpilih</strong><br>
                Lat: ${latlng.lat.toFixed(6)}<br>
                Lng: ${latlng.lng.toFixed(6)}
            </div>
        `
            )
            .openPopup();
    }

    updateCoordinates(lat, lng) {
        const latInput = document.getElementById(this.latitudeInput);
        const lngInput = document.getElementById(this.longitudeInput);

        if (latInput) latInput.value = lat.toFixed(8);
        if (lngInput) lngInput.value = lng.toFixed(8);
    }

    updateLocationInput(locationName) {
        const locationInput = document.getElementById(this.locationInput);
        const addressInput = document.getElementById(this.addressInput);

        if (locationInput) locationInput.value = locationName;
        if (addressInput) addressInput.value = locationName;
    }

    reverseGeocode(lat, lng) {
        // Reverse geocoding to get address
        fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`
        )
            .then((response) => response.json())
            .then((data) => {
                if (data.display_name) {
                    this.updateLocationInput(data.display_name);
                }
            })
            .catch((error) => {
                console.error("Reverse geocoding error:", error);
            });
    }

    loadExistingLocation() {
        const latInput = document.getElementById(this.latitudeInput);
        const lngInput = document.getElementById(this.longitudeInput);

        if (latInput && lngInput && latInput.value && lngInput.value) {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);

            if (!isNaN(lat) && !isNaN(lng)) {
                this.setMarker([lat, lng]);
                this.map.setView([lat, lng], 15);
            }
        }
    }

    // Public methods
    getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    this.setMarker([lat, lng]);
                    this.updateCoordinates(lat, lng);
                    this.map.setView([lat, lng], 15);
                    this.reverseGeocode(lat, lng);
                },
                (error) => {
                    console.error("Geolocation error:", error);
                    alert(
                        "Tidak dapat mengakses lokasi saat ini. Silakan pilih lokasi secara manual."
                    );
                }
            );
        } else {
            alert("Browser tidak mendukung geolocation.");
        }
    }

    clearLocation() {
        if (this.marker) {
            this.map.removeLayer(this.marker);
            this.marker = null;
        }

        const latInput = document.getElementById(this.latitudeInput);
        const lngInput = document.getElementById(this.longitudeInput);
        const locationInput = document.getElementById(this.locationInput);
        const addressInput = document.getElementById(this.addressInput);

        if (latInput) latInput.value = "";
        if (lngInput) lngInput.value = "";
        if (locationInput) locationInput.value = "";
        if (addressInput) addressInput.value = "";
    }
}

// Initialize maps when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    // Initialize activity maps
    const activityMapContainer = document.getElementById("activity-map");
    if (activityMapContainer) {
        window.activityMap = new LeafletMaps("activity-map", {
            latitudeInput: "latitude",
            longitudeInput: "longitude",
            locationInput: "location",
            isResidence: false,
        });
    }

    // Initialize residence maps
    const residenceMapContainer = document.getElementById("residence-map");
    if (residenceMapContainer) {
        window.residenceMap = new LeafletMaps("residence-map", {
            latitudeInput: "latitude",
            longitudeInput: "longitude",
            addressInput: "address",
            isResidence: true,
        });
    }
});







