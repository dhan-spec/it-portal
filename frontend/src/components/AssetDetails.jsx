import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { API_BASE_URL } from '../config';
import { 
    Clock, 
    ArrowLeft, 
    User, 
    Wrench, 
    PlusCircle,
    CheckCircle,
    Cpu,
    MonitorSmartphone,
    X
} from 'lucide-react';
import './AssetDetails.css';

const AssetDetails = () => {
    const { tag } = useParams();
    const navigate = useNavigate();
    const [asset, setAsset] = useState(null);
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const token = localStorage.getItem('portal_token');
                const [assetRes, logsRes] = await Promise.all([
                    fetch(`${API_BASE_URL}/asset_detail.php?tag=${tag}`, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    }),
                    fetch(`${API_BASE_URL}/logs.php?asset_tag=${tag}`, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    })
                ]);
                
                const assetData = await assetRes.json();
                const logsData = await logsRes.json();
                
                setAsset(assetData);
                setLogs(Array.isArray(logsData) ? logsData : []);
            } catch (err) {
                console.error("Failed to fetch asset details:", err);
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [tag]);

    if (loading) return <div className="loader-container">...Loading System...</div>;
    if (!asset) return <div className="error-container">Asset not found.</div>;

    return (
        <div className="asset-details-page">
            <header className="details-header">
                <button className="back-btn" onClick={() => navigate('/')}>
                    <ArrowLeft size={18} /> Back to Inventory
                </button>
                <div className="header-actions">
                   <h1 className="asset-tag-badge">{asset.asset_tag}</h1>
                </div>
            </header>

            <div className="details-grid">
                <section className="asset-summary cinematic-card">
                    <div className="asset-hero">
                        <MonitorSmartphone size={64} className="hero-icon" />
                        <h2>{asset.name}</h2>
                        <span className="status-pill online">{asset.status || 'Active'}</span>
                    </div>
                    
                    <div className="spec-grid">
                        <div className="spec-item">
                            <span className="label">Location</span>
                            <span className="value">{asset.location || 'N/A'}</span>
                        </div>
                        <div className="spec-item">
                            <span className="label">IP Address</span>
                            <span className="value">{asset.ip_address || 'DHCP'}</span>
                        </div>
                        <div className="spec-item">
                            <span className="label">Hardware Type</span>
                            <span className="value">{asset.type}</span>
                        </div>
                    </div>
                </section>

                <section className="maintenance-timeline">
                    <div className="section-header">
                        <Clock size={20} />
                        <h3>Maintenance Timeline</h3>
                    </div>

                    <div className="timeline-container">
                        {logs.length === 0 ? (
                            <p className="empty-timeline">No history recorded yet.</p>
                        ) : (
                            logs.map((log) => (
                                <div key={log.id} className="timeline-item">
                                    <div className="timeline-marker"></div>
                                    <div className="timeline-content cinematic-card">
                                        <div className="log-meta">
                                            <span className="log-date">{new Date(log.date).toLocaleDateString()}</span>
                                            <span className="log-tech"><User size={12} /> {log.technician}</span>
                                        </div>
                                        <p className="log-desc">{log.description}</p>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </section>
            </div>
        </div>
    );
};

export default AssetDetails;
