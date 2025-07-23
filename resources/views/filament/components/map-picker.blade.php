<div>
    <input type="hidden" id="latitude" name="latitude" wire:model="data.latitude">
    <input type="hidden" id="longitude" name="longitude" wire:model="data.longitude">

    <div id="map-container" wire:ignore>
        <div id="map" style="width: 100%; height: 300px; background-color: #e5e7eb;"></div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&callback=initMap" async
    defer></script>

<script>
    let map;
    let marker;
    let isInitialized = false;

    function updateHiddenFields(latLng) {
        @this.set('data.latitude', latLng.lat());
        @this.set('data.longitude', latLng.lng());
    }

    function initMap() {
        if (isInitialized) return;
        isInitialized = true;

        const initialLat = parseFloat(@js($latitude)) || 30.0444;
        const initialLng = parseFloat(@js($longitude)) || 31.2357;
        const initialPosition = { lat: initialLat, lng: initialLng };

        map = new google.maps.Map(document.getElementById('map'), {
            center: initialPosition,
            zoom: 13,
            gestureHandling: 'cooperative'
        });

        marker = new google.maps.Marker({
            position: initialPosition,
            map: map,
            draggable: true
        });

        map.addListener('click', function (event) {
            marker.setPosition(event.latLng);
            updateHiddenFields(event.latLng);
        });

        marker.addListener('dragend', function (event) {
            updateHiddenFields(event.latLng);
        });

        google.maps.event.addDomListener(window, 'resize', function () {
            const center = map.getCenter();
            google.maps.event.trigger(map, 'resize');
            map.setCenter(center);
        });
    }

    window.initMap = initMap;

    document.addEventListener('livewire:init', function () {
        Livewire.hook('element.initialized', (el, component) => {
            if (el.id === 'map' && typeof google !== 'undefined') {
                initMap();
            }
        });

        Livewire.hook('message.processed', () => {
            if (typeof google !== 'undefined' && typeof map !== 'undefined') {
                const center = marker.getPosition();
                google.maps.event.trigger(map, 'resize');
                map.setCenter(center);
                marker.setMap(map);
            }
        });

    });
</script>