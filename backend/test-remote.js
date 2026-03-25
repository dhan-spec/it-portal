const mysql = require('mysql2');
require('dotenv').config();

// Create connection
const connection = mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT || 3306 // Hostinger often uses default 3306, but good to have
});

console.log('--- Database Connection Test ---');
console.log(`Connecting to: ${process.env.DB_HOST}`);
console.log(`User: ${process.env.DB_USER}`);
console.log(`Database: ${process.env.DB_NAME}`);
console.log('---------------------------------');

connection.connect((err) => {
    if (err) {
        console.error('❌ Connection failed!');
        console.error('Error details:', err.message);
        process.exit(1);
    }

    console.log('✅ Connection successful!');

    // Query to check if the 'assets' table exists
    const query = `
        SELECT COUNT(*) as count 
        FROM information_schema.tables 
        WHERE table_schema = ? 
        AND table_name = 'assets'
    `;

    connection.query(query, [process.env.DB_NAME], (error, results) => {
        if (error) {
            console.error('❌ Error checking for table:', error.message);
            connection.end();
            process.exit(1);
        }

        const count = results[0].count;
        if (count > 0) {
            console.log("✅ The 'assets' table exists.");
        } else {
            console.log("⚠️ The 'assets' table does NOT exist yet.");
        }

        connection.end();
    });
});
