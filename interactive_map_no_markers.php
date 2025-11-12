```html
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <script>
    L_NO_TOUCH = false;
    L_DISABLE_3D = false;
  </script>

  <style>
    html, body {
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
    }
    #map {
      position: absolute;
      top: 0;
      bottom: 0;
      right: 0;
      left: 0;
    }
    #loc-info {
      position: absolute;
      z-index: 1000;
      right: 10px;
      top: 70px;
      background: rgba(255, 255, 255, 0.9);
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 13px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }
    #loc-now {
      margin-top: 6px;
      padding: 6px 10px;
      cursor: pointer;
      border: none;
      border-radius: 6px;
      background: #2196f3;
      color: white;
    }
    #loc-now:hover {
      background: #1976d2;
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/leaflet@1.6.0/dist/leaflet.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.6.0/dist/leaflet.css"/>
</head>

<body>

  <!-- Map -->
  <div id="map"></div>

  <!-- Info box -->
  <div id="loc-info">
    <div id="loc-status">Loc: waiting...</div>
    <div id="loc-accuracy"></div>
    <button id="loc-now">Get current</button>
  </div>

  <script>
    // Initialize map centered at Manila
    var map = L.map("map").setView([14.5995, 120.9842], 12);

    // Add OpenStreetMap tiles
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);

    // Marker and accuracy circle
    var userMarker = L.marker([14.5995, 120.9842]).addTo(map);
    var accuracyCircle = null;

    const ACCURACY_THRESHOLD = 80; // meters

    function handlePosition(position) {
      const lat = position.coords.latitude;
      const lon = position.coords.longitude;
      const acc = position.coords.accuracy;
      const ts = new Date(position.timestamp);

      document.getElementById("loc-status").textContent = `Lat: ${lat.toFixed(6)}, Lon: ${lon.toFixed(6)}`;
      document.getElementById("loc-accuracy").textContent = `Accuracy: ±${Math.round(acc)} m (Last: ${ts.toLocaleTimeString()})`;

      // Skip low accuracy
      if (acc > ACCURACY_THRESHOLD) {
        console.warn("Low accuracy:", acc);
        userMarker.bindPopup(`Low accuracy (${Math.round(acc)} m). Move to open area.`).openPopup();
        return;
      }

      // Update marker and accuracy circle
      userMarker.setLatLng([lat, lon]);
      if (!accuracyCircle) {
        accuracyCircle = L.circle([lat, lon], { radius: acc, color: "#3388ff", fillOpacity: 0.15 }).addTo(map);
      } else {
        accuracyCircle.setLatLng([lat, lon]);
        accuracyCircle.setRadius(acc);
      }

      map.panTo([lat, lon], { animate: true, duration: 0.5 });
      userMarker.bindPopup(`📍 You are here<br>Accuracy: ±${Math.round(acc)} m`).openPopup();
      console.log("GPS update:", lat, lon, "accuracy:", acc);
    }

    function handleError(error) {
      console.error("GPS error:", error);
      document.getElementById("loc-status").textContent = `Error: ${error.message}`;
    }

    // Start watching position
    if ("geolocation" in navigator) {
      navigator.geolocation.watchPosition(
        handlePosition,
        handleError,
        { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 }
      );
    } else {
      alert("Geolocation not supported by this browser.");
    }

    // Manual refresh button
    document.getElementById("loc-now").addEventListener("click", function () {
      if (!("geolocation" in navigator)) {
        return alert("Geolocation not supported");
      }
      navigator.geolocation.getCurrentPosition(handlePosition, handleError, {
        enableHighAccuracy: true,
        maximumAge: 0,
        timeout: 20000,
      });
    });
  </script>
</body>
</html>
```
