const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');
const jwt = require('jsonwebtoken');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 5000;

app.use(cors());
app.use(express.json());

// MySQL Connection Pool (better for handling multiple connections)
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT || 3306,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Middleware to protect routes
const authenticateToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    
    if (!token) return res.status(401).json({ error: 'Access denied. No token provided.' });

    jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
        if (err) return res.status(403).json({ error: 'Invalid or expired token.' });
        req.user = user;
        next();
    });
};

// POST: Login endpoint
app.post('/api/login', (req, res) => {
    const { password } = req.body;
    
    if (password === process.env.ADMIN_PASSWORD) {
        const token = jwt.sign({ role: 'admin' }, process.env.JWT_SECRET, { expiresIn: '24h' });
        res.json({ token });
    } else {
        res.status(401).json({ error: 'Invalid password' });
    }
});

// GET: List all assets
app.get('/api/assets', authenticateToken, async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM assets ORDER BY created_at DESC');
        res.json(rows);
    } catch (error) {
        console.error('Error fetching assets:', error);
        res.status(500).json({ error: 'Database error' });
    }
});

// GET: Single asset by Tag
app.get('/api/assets/:tag', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM assets WHERE asset_tag = ? LIMIT 1', [req.params.tag]);
        if (rows.length === 0) {
            return res.status(404).json({ error: 'Asset not found' });
        }
        res.json(rows[0]);
    } catch (error) {
        console.error('Error fetching asset:', error);
        res.status(500).json({ error: 'Database error' });
    }
});

// POST: Add new asset
app.post('/api/assets', authenticateToken, async (req, res) => {
    const { name, asset_tag, type, location, ip_address } = req.body;
    
    if (!name || !asset_tag || !type) {
        return res.status(400).json({ error: 'Name, Asset Tag, and Type are required' });
    }

    try {
        const query = `
            INSERT INTO assets (name, asset_tag, type, location, ip_address) 
            VALUES (?, ?, ?, ?, ?)
        `;
        const [result] = await pool.query(query, [name, asset_tag, type, location, ip_address]);
        
        res.status(201).json({ 
            message: 'Asset added successfully', 
            id: result.insertId 
        });
    } catch (error) {
        if (error.code === 'ER_DUP_ENTRY') {
            return res.status(400).json({ error: 'Asset tag already exists' });
        }
        console.error('Error adding asset:', error);
        res.status(500).json({ error: 'Database error' });
    }
});

app.get('/', (req, res) => {
    res.send('IT Management Portal API is running');
});

app.listen(port, () => {
    console.log(`Server is running on port ${port}`);
});
