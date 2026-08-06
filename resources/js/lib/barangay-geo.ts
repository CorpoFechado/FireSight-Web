/**
 * The GeoJSON boundary file (public/geo/lian-per-barangay.geojson) uses the
 * five poblacion barangays' traditional Filipino/Spanish-numeral names,
 * while the `barangay` table stores them as "Poblacion 1"–"Poblacion 5".
 * Every other barangay name matches exactly. This maps GeoJSON name -> DB
 * `barangay_name` so the polygon layer can be joined to risk data.
 */
export const GEOJSON_TO_DB_BARANGAY_NAME: Record<string, string> = {
    Uno: 'Poblacion 1',
    Dos: 'Poblacion 2',
    Tres: 'Poblacion 3',
    Kwatro: 'Poblacion 4',
    Singko: 'Poblacion 5',
};

/** Resolves a GeoJSON feature's `barangay` property to the DB's `barangay_name`. */
export function resolveDbBarangayName(geoJsonName: string): string {
    return GEOJSON_TO_DB_BARANGAY_NAME[geoJsonName] ?? geoJsonName;
}

export const BARANGAY_GEOJSON_URL = '/geo/lian-per-barangay.geojson';
