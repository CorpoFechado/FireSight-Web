/**
 * The GeoJSON boundary file (`public/geo/barangay_boundaries_lian.json`) uses
 * official Philippine administrative names in `adm4_name`
 * (e.g. "Barangay 1 (Pob.)"–"Barangay 5 (Pob.)", "Puting-Kahoy"),
 * while the `barangay` table stores them as "Barangay 1 (Poblacion)", "Puting Kahoy", etc.
 *
 * This mapping joins GeoJSON feature names to the database `barangay_name`
 * so polygon overlays match the risk assessments and incident counts.
 */
export const GEOJSON_TO_DB_BARANGAY_NAME: Record<string, string> = {
    // adm4_name format (from barangay_boundaries_lian.json):
    'Barangay 1 (Pob.)': 'Barangay 1 (Poblacion)',
    'Barangay 2 (Pob.)': 'Barangay 2 (Poblacion)',
    'Barangay 3 (Pob.)': 'Barangay 3 (Poblacion)',
    'Barangay 4 (Pob.)': 'Barangay 4 (Poblacion)',
    'Barangay 5 (Pob.)': 'Barangay 5 (Poblacion)',
    'Puting-Kahoy': 'Puting Kahoy',

    // Fallback aliases for legacy or alternate GeoJSON sources:
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

/**
 * Extracts the raw barangay name from any GeoJSON feature properties object
 * (supports `adm4_name`, `barangay`, `name`, `BGY_NAME`).
 */
export function getFeatureBarangayName(properties?: Record<string, any> | null): string {
    if (!properties) {
        return '';
    }

    return (
        properties.barangay_name ||
        properties.adm4_name ||
        properties.barangay ||
        properties.name ||
        properties.BGY_NAME ||
        ''
    );
}

/** Resolves a GeoJSON feature name to the DB's `barangay_name`. */
export function resolveDbBarangayName(geoJsonName: string): string {
    return GEOJSON_TO_DB_BARANGAY_NAME[geoJsonName] ?? geoJsonName;
}

export const BARANGAY_GEOJSON_URL = '/geo/barangay_boundaries_lian.json';
