<?php
/**
 * Region Boundary Functions
 * 
 * This file provides functions for managing GeoJSON boundaries for regions.
 */

/**
 * Creates a simple GeoJSON boundary for a region based on its coordinates
 * 
 * @param float $lat Latitude of the region center
 * @param float $lng Longitude of the region center
 * @param string $name Name of the region
 * @param float $radius Radius for the region boundary in degrees (approx)
 * @param int $points Number of points to generate for the boundary
 * @return array GeoJSON Feature object
 */
function createSimpleBoundary($lat, $lng, $name, $radius = 0.015, $points = 6) {
    // Generate a simple polygon (hexagon by default)
    $coordinates = [];
    
    for ($i = 0; $i < $points; $i++) {
        $angle = 2 * M_PI * $i / $points;
        // Add some randomness to make it look more natural
        $variationFactor = 0.8 + (mt_rand(0, 40) / 100); // 0.8 to 1.2
        $r = $radius * $variationFactor;
        
        $coordinates[] = [$lng + $r * cos($angle), $lat + $r * sin($angle)];
    }
    
    // Close the polygon
    $coordinates[] = $coordinates[0];
    
    // Create GeoJSON feature
    return [
        'type' => 'Feature',
        'properties' => [
            'name' => $name
        ],
        'geometry' => [
            'type' => 'Polygon',
            'coordinates' => [$coordinates]
        ]
    ];
}

/**
 * Creates a more complex, irregular boundary to better simulate real boundaries
 * 
 * @param float $lat Latitude of the region center
 * @param float $lng Longitude of the region center
 * @param string $name Name of the region
 * @param float $baseRadius Base radius for the region
 * @param int $points Number of vertices to generate
 * @return array GeoJSON Feature object
 */
function createComplexBoundary($lat, $lng, $name, $baseRadius = 0.015, $points = 12) {
    // Generate a complex polygon with irregular edges
    $coordinates = [];
    
    // Create the initial polygon
    for ($i = 0; $i < $points; $i++) {
        $angle = 2 * M_PI * $i / $points;
        
        // Add randomness to radius (more variation)
        $radiusVariation = 0.6 + (mt_rand(0, 80) / 100); // 0.6 to 1.4
        $r = $baseRadius * $radiusVariation;
        
        // Add small variation to angle
        $angleVariation = (mt_rand(-10, 10) / 100) * (M_PI / 8); // ±10% of π/8
        $finalAngle = $angle + $angleVariation;
        
        $coordinates[] = [$lng + $r * cos($finalAngle), $lat + $r * sin($finalAngle)];
    }
    
    // Close the polygon
    $coordinates[] = $coordinates[0];
    
    // Create GeoJSON feature
    return [
        'type' => 'Feature',
        'properties' => [
            'name' => $name
        ],
        'geometry' => [
            'type' => 'Polygon',
            'coordinates' => [$coordinates]
        ]
    ];
}

/**
 * Generates a complete GeoJSON FeatureCollection from region coordinates
 * 
 * @param PDO $pdo Database connection
 * @param bool $complex Whether to create complex (true) or simple (false) boundaries
 * @return array GeoJSON FeatureCollection
 */
function generateBoundariesFromCoordinates($pdo, $complex = true) {
    // Fetch all region coordinates
    $stmt = $pdo->query("SELECT wilayah, latitude, longitude FROM region_coordinates");
    $regions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $features = [];
    
    foreach ($regions as $region) {
        $lat = (float)$region['latitude'];
        $lng = (float)$region['longitude'];
        $name = $region['wilayah'];
        
        // Create boundary based on complexity option
        if ($complex) {
            $features[] = createComplexBoundary($lat, $lng, $name);
        } else {
            $features[] = createSimpleBoundary($lat, $lng, $name);
        }
    }
    
    // Create GeoJSON FeatureCollection
    return [
        'type' => 'FeatureCollection',
        'features' => $features
    ];
}

/**
 * Saves a GeoJSON object to file
 * 
 * @param array $geojson GeoJSON data to save
 * @param string $file Path to save the file
 * @return bool Success status
 */
function saveGeoJSONToFile($geojson, $file) {
    $directory = dirname($file);
    
    // Create directory if it doesn't exist
    if (!is_dir($directory)) {
        if (!mkdir($directory, 0755, true)) {
            return false;
        }
    }
    
    // Convert to JSON and save
    $json = json_encode($geojson, JSON_PRETTY_PRINT);
    return file_put_contents($file, $json) !== false;
}

/**
 * Loads GeoJSON data from file
 * 
 * @param string $file Path to the GeoJSON file
 * @return array|null The GeoJSON data, or null if unable to load
 */
function loadGeoJSONFromFile($file) {
    if (!file_exists($file)) {
        return null;
    }
    
    $content = file_get_contents($file);
    if ($content === false) {
        return null;
    }
    
    $geojson = json_decode($content, true);
    if ($geojson === null) {
        return null;
    }
    
    return $geojson;
}

/**
 * Validates a GeoJSON array to ensure it has the correct structure
 * 
 * @param array $geojson The GeoJSON data to validate
 * @return array Validation result ['valid' => bool, 'message' => string]
 */
function validateGeoJSON($geojson) {
    if (!is_array($geojson)) {
        return ['valid' => false, 'message' => 'Invalid GeoJSON: not an array or object.'];
    }
    
    if (!isset($geojson['type']) || $geojson['type'] !== 'FeatureCollection') {
        return ['valid' => false, 'message' => 'Invalid GeoJSON: must be a FeatureCollection.'];
    }
    
    if (!isset($geojson['features']) || !is_array($geojson['features'])) {
        return ['valid' => false, 'message' => 'Invalid GeoJSON: features property missing or not an array.'];
    }
    
    foreach ($geojson['features'] as $index => $feature) {
        if (!isset($feature['type']) || $feature['type'] !== 'Feature') {
            return ['valid' => false, 'message' => "Invalid GeoJSON: feature $index must have type 'Feature'."];
        }
        
        if (!isset($feature['properties']) || !is_array($feature['properties'])) {
            return ['valid' => false, 'message' => "Invalid GeoJSON: feature $index missing properties."];
        }
        
        if (!isset($feature['properties']['name'])) {
            return ['valid' => false, 'message' => "Invalid GeoJSON: feature $index missing name property."];
        }
        
        if (!isset($feature['geometry']) || !is_array($feature['geometry'])) {
            return ['valid' => false, 'message' => "Invalid GeoJSON: feature $index missing geometry."];
        }
        
        if (!isset($feature['geometry']['type']) || !in_array($feature['geometry']['type'], ['Polygon', 'MultiPolygon'])) {
            return ['valid' => false, 'message' => "Invalid GeoJSON: feature $index geometry must be Polygon or MultiPolygon."];
        }
        
        if (!isset($feature['geometry']['coordinates']) || !is_array($feature['geometry']['coordinates'])) {
            return ['valid' => false, 'message' => "Invalid GeoJSON: feature $index missing coordinates."];
        }
    }
    
    return ['valid' => true, 'message' => 'GeoJSON is valid.'];
}