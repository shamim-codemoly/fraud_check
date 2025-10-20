<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Check</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-2xl text-center bg-white p-8 rounded-2xl shadow-lg">
        <h1 class="text-3xl font-bold text-blue-600 mb-4">Courier Data Check</h1>
        <p class="text-gray-600 mb-6">Enter an 11-digit phone number to check courier records.</p>

        <!-- Input -->
        <div class="flex gap-2 justify-center mb-6">
            <input id="phoneInput" type="text" maxlength="11" placeholder="Enter phone number"
                class="w-2/3 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button onclick="checkCourier()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Check
            </button>
        </div>

        <!-- Result -->
        <div id="resultBox" class="hidden">
            <table class="w-full text-sm text-left border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border">Courier</th>
                        <th class="px-4 py-2 border">Total</th>
                        <th class="px-4 py-2 border">Success</th>
                        <th class="px-4 py-2 border">Cancel</th>
                    </tr>
                </thead>
                <tbody id="courierTableBody" class="text-gray-600"></tbody>
            </table>

            <!-- Summary -->
            <div class="flex justify-around mt-4 text-sm">
                <span id="totalBox" class="px-4 py-2 rounded bg-gray-200 font-semibold">Total: 0</span>
                <span id="successBox" class="px-4 py-2 rounded bg-green-500 text-white font-semibold">Success: 0</span>
                <span id="cancelBox" class="px-4 py-2 rounded bg-red-500 text-white font-semibold">Cancel: 0</span>
            </div>

            <div id="ratioBox" class="mt-4 px-4 py-2 bg-green-100 text-green-700 font-bold rounded"></div>
        </div>
    </div>

    <script>
        async function checkCourier() {
            const phone = document.getElementById('phoneInput').value.trim();
            if (!/^\d{11}$/.test(phone)) {
                alert("Please enter a valid 11-digit phone number.");
                return;
            }

            const resultBox = document.getElementById('resultBox');
            const tableBody = document.getElementById('courierTableBody');
            const totalBox = document.getElementById('totalBox');
            const successBox = document.getElementById('successBox');
            const cancelBox = document.getElementById('cancelBox');
            const ratioBox = document.getElementById('ratioBox');

            resultBox.classList.remove("hidden");
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4">Fetching data...</td></tr>`;
            totalBox.textContent = "Total: 0";
            successBox.textContent = "Success: 0";
            cancelBox.textContent = "Cancel: 0";
            ratioBox.textContent = "";

            try {
                const response = await fetch("/api/customer/fraud-check", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },
                    body: JSON.stringify({
                        api_key: "SAFZIJ==APIKEY=FC=LIVE=CHECKING",
                        phone: phone
                    }),
                });

                const data = await response.json();
                if (!data.courierData) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4">No data found</td></tr>`;
                    return;
                }

                const couriers = ["pathao", "steadfast", "redx", "paperfly"];
                tableBody.innerHTML = "";
                couriers.forEach(courier => {
                    const c = data.courierData[courier] || {
                        total_parcel: 0,
                        success_parcel: 0,
                        cancelled_parcel: 0
                    };
                    tableBody.innerHTML += `
                        <tr>
                            <td class="px-4 py-2 border capitalize">${courier}</td>
                            <td class="px-4 py-2 border">${c.total_parcel}</td>
                            <td class="px-4 py-2 border">${c.success_parcel}</td>
                            <td class="px-4 py-2 border">${c.cancelled_parcel}</td>
                        </tr>
                    `;
                });

                const summary = data.courierData.summary || {
                    total_parcel: 0,
                    success_parcel: 0,
                    cancelled_parcel: 0,
                    success_ratio: 0
                };
                totalBox.textContent = `Total: ${summary.total_parcel}`;
                successBox.textContent = `Success: ${summary.success_parcel}`;
                cancelBox.textContent = `Cancel: ${summary.cancelled_parcel}`;

                const successRate = summary.success_ratio || 0;
                const cancelRate = summary.total_parcel > 0 ? ((summary.cancelled_parcel / summary.total_parcel) * 100)
                    .toFixed(1) : 0;
                ratioBox.textContent = `${successRate}% Success / ${cancelRate}% Cancel`;
            } catch (error) {
                tableBody.innerHTML =
                    `<tr><td colspan="4" class="text-center py-4 text-red-500">Error fetching data</td></tr>`;
            }
        }
    </script>
</body>

</html>
