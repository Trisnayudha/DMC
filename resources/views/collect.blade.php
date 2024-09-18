<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Collect Exhibitor Data</title>
</head>

<body>

    <h1>Auto Collect Exhibitor Data</h1>
    <button onclick="collectData()">Start Collecting Data</button>

    <script>
        const ids = [560, 561, 562]; // Replace with your list of IDs
        const formRoute = 'https://your-form-route.com'; // Replace with your actual form route

        async function collectData() {
            for (let id of ids) {
                try {
                    // Fetch data for each exhibitor based on ID using GET method
                    const response = await fetch(`https://vexpo.iee-series.com/iee/pc/exhibitor/${id}`, {
                        method: 'GET'
                    });
                    const data = await response.json();

                    if (data && data.code === 200) {
                        const exhibitorData = {
                            name: data.data.name,
                            country: data.data.country,
                            desc: data.data.desc || 'N/A',
                            website: data.data.website || 'N/A',
                            contact: data.data.contact || 'N/A',
                            contactEmail: data.data.contactEmail || 'N/A',
                            displayEmail: data.data.displayEmail || 'N/A',
                            venueHall: data.data.venueHall || 'N/A',
                            eventName: data.data.eventName || 'N/A',
                            exhibitorLogo: data.data.exhibitorLogo || 'N/A',
                            boothNumber: data.data.boothNumber || 'N/A',
                            category1: data.data.category1 || 'N/A',
                            category2: data.data.category2 || 'N/A'
                        };

                        // Submit the data to the form route
                        await submitToForm(exhibitorData);
                    } else {
                        console.error(`Failed to collect data for ID: ${id}`);
                    }
                } catch (error) {
                    console.error(`Error fetching data for ID: ${id}`, error);
                }
            }
        }

        async function submitToForm(exhibitorData) {
            try {
                const response = await fetch(formRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(exhibitorData)
                });

                const result = await response.json();
                console.log(`Data submitted for ${exhibitorData.name}:`, result);
            } catch (error) {
                console.error(`Error submitting data for ${exhibitorData.name}`, error);
            }
        }
    </script>

</body>

</html>
