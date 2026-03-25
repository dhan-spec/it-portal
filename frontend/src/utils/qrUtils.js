/**
 * Generates the URL for scanning an IT asset tag.
 * 
 * @param {string} assetTag - The unique identifier/tag of the asset.
 * @returns {string} The full URL to be encoded in the QR code.
 */
export const generateAssetQRUrl = (assetTag) => {
    if (!assetTag) return '';
    return `https://it.websitebuilderph.com/scan/${encodeURIComponent(assetTag)}`;
};
