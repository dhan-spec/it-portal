const mysql = require('mysql2/promise');
require('dotenv').config();

async function setupDatabase() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME,
        port: process.env.DB_PORT || 3306,
        multipleStatements: true // Allow multiple SQL statements
    });

    console.log('--- Database Setup ---');
    console.log(`Connecting to: ${process.env.DB_HOST}`);

    const sql = `
        CREATE TABLE IF NOT EXISTS assets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            asset_tag VARCHAR(50) UNIQUE NOT NULL, 
            name VARCHAR(100) NOT NULL,
            type ENUM('POS', 'Printer', 'Access Point', 'Server', 'Workstation') NOT NULL,
            location VARCHAR(100),
            ip_address VARCHAR(15),
            status ENUM('Online', 'Offline', 'Under Repair') DEFAULT 'Online',
            last_maintenance DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS maintenance_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            asset_id INT,
            action_taken TEXT,
            performed_by VARCHAR(100),
            log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
        );
    `;

    try {
        console.log('Creating tables...');
        await connection.query(sql);
        console.log('✅ Tables "assets" and "maintenance_logs" created successfully!');

        // Verify tables
        const [rows] = await connection.query('SHOW TABLES');
        console.log('Current tables in database:');
        rows.forEach(row => {
            console.log(`- ${Object.values(row)[0]}`);
        });

    } catch (error) {
        console.error('❌ Error during setup:', error.message);
    } finally {
        await connection.end();
    }
}

setupDatabase();
