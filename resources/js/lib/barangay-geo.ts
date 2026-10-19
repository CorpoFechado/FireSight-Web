/**
 * The GeoJSON boundary file (public/geo/lian-per-barangay.geojson) uses the
 * five poblacion barangays' traditional Filipino/Spanish-numeral names,
 * while the `barangay` table stores them as "Poblacion 1"–"Poblacion 5".
 * Every other barangay name matches exactly. This maps GeoJSON name -> DB
 * `barangay_name` so the polygon layer can be joined to risk data.
 */
export const GEOJSON_TO_DB_BARANGAY_NAME: Record<string, string> = {
    Uno: 'Barangay 1 (Poblacion)',
    Dos: 'Barangay 2 (Poblacion)',
    Tres: 'Barangay 3 (Poblacion)',
    Kwatro: 'Barangay 4 (Poblacion)',
    Singko: 'Barangay 5 (Poblacion)',
    'Poblacion 1': 'Barangay 1 (Poblacion)',
    'Poblacion 2': 'Barangay 2 (Poblacion)',
    'Poblacion 3': 'Barangay 3 (Poblacion)',
    'Poblacion 4': 'Barangay 4 (Poblacion)',
    'Poblacion 5': 'Barangay 5 (Poblacion)',
};

/** Resolves a GeoJSON feature's `barangay` property to the DB's `barangay_name`. */
export function resolveDbBarangayName(geoJsonName: string): string {
    return GEOJSON_TO_DB_BARANGAY_NAME[geoJsonName] ?? geoJsonName;
}

export const BARANGAY_GEOJSON_URL = '/geo/lian-per-barangay.geojson';
