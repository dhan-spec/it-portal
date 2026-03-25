import { useState, useEffect } from 'react';
import { QRCodeSVG } from 'qrcode.react';
import { generateAssetQRUrl } from '../utils/qrUtils';
import './AssetList.css'; // Let's create a basic CSS file for this too

const AssetList = () => {
    const [assets, setAssets] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeQR, setActiveQR] = useState(null); // Tracks which asset's QR is visible

    useEffect(() => {
        const fetchAssets = async () => {
            try {
                // Assuming backend is running on localhost:5000
                const response = await fetch('http://localhost:5000/api/assets');
                if (!response.ok) {
                    throw new Error('Failed to fetch assets');
                }
                const data = await response.json();
                setAssets(data);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchAssets();
    }, []);

    const toggleQR = (assetId) => {
        if (activeQR === assetId) {
            setActiveQR(null); // Hide if already clicked
        } else {
            setActiveQR(assetId); // Show for clicked asset
        }
    };

    if (loading) return <div>Loading assets...</div>;
    if (error) return <div className="error">Error: {error}</div>;

    return (
        <div className="asset-list-container">
            <h2>IT Asset Inventory</h2>
            {assets.length === 0 ? (
                <p>No assets found. Database might be empty.</p>
            ) : (
                <div className="asset-grid">
                    {assets.map((asset) => (
                        <div key={asset.id} className="asset-card">
                            <div className="asset-info">
                                <h3>{asset.name}</h3>
                                <p><strong>Tag:</strong> {asset.asset_tag}</p>
                                <p><strong>Type:</strong> {asset.type}</p>
                                <p><strong>Location:</strong> {asset.location || 'N/A'}</p>
                                <p><strong>IP:</strong> {asset.ip_address || 'N/A'}</p>
                            </div>
                            <div className="qr-section">
                                <button className="qr-button" onClick={() => toggleQR(asset.id)}>
                                    {activeQR === asset.id ? 'Hide QR Code' : 'Generate QR Code'}
                                </button>
                                
                                {activeQR === asset.id && (
                                    <div className="qr-code-display">
                                        <QRCodeSVG 
                                            value={generateAssetQRUrl(asset.asset_tag)} 
                                            size={128}
                                            level={"L"}
                                            includeMargin={true}
                                        />
                                        <p className="qr-url-hint">
                                            Points to: {generateAssetQRUrl(asset.asset_tag)}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default AssetList;
