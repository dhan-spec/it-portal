const BASE_URL = 'http://localhost:5000/api/assets';

async function testAPI() {
    console.log('--- Testing IT Asset API ---');

    // 1. Test POST /api/assets
    console.log('\nTesting POST /api/assets...');
    const newAsset = {
        name: 'Main Server Room AP',
        asset_tag: 'AP-002',
        type: 'Access Point',
        location: 'Server Room',
        ip_address: '192.168.1.50'
    };

    try {
        const postRes = await fetch(BASE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newAsset)
        });
        const postData = await postRes.json();
        if (postRes.ok) {
            console.log('✅ POST Successful!', postData);
        } else {
            console.log('❌ POST Failed:', postData);
        }
    } catch (error) {
        console.error('❌ POST Error:', error.message);
    }

    // 2. Test GET /api/assets
    console.log('\nTesting GET /api/assets...');
    try {
        const getRes = await fetch(BASE_URL);
        const getData = await getRes.json();
        if (getRes.ok) {
            console.log('✅ GET Successful!');
            console.log(`Found ${getData.length} assets.`);
            if (getData.length > 0) {
                console.log('First asset:', getData[0]);
            }
        } else {
            console.log('❌ GET Failed:', getData);
        }
    } catch (error) {
        console.error('❌ GET Error:', error.message);
    }
}

testAPI();
