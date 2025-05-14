<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bank Stock Viewer</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f8f9fa;
            padding: 40px;
            color: #333;
        }

        h1 {
            color: #343a40;
            margin-bottom: 30px;
            text-align: center;
        }

        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            width: 80%;
            max-width: 400px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 15px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        table {
            width: 90%;
            margin: 0 auto 40px auto;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
            font-size: 16px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        canvas {
            margin: 0 auto;
            display: block;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 900px;
        }

        @media (max-width: 600px) {
            table, canvas {
                width: 100%;
            }

            th, td {
                padding: 10px;
                font-size: 14px;
            }

            h1 {
                font-size: 24px;
            }

            button {
                font-size: 12px;
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>

    <h1>📈 Live Bank Stock Prices</h1>

    <!-- Search Box -->
    <div class="search-container">
        <input type="text" id="searchBox" placeholder="Search for a bank or symbol...">
        <button onclick="filterTable()">Search</button>
    </div>

    <table>
        <thead>
            <tr><th>Bank</th><th>Symbol</th><th>Price</th><th>Chart</th></tr>
        </thead>
        <tbody id="stock-table"></tbody>
    </table>

    <canvas id="chart" width="600" height="300"></canvas>

    <script>
        let chart;

        document.addEventListener('DOMContentLoaded', () => {
            fetchStocks();
            setInterval(refreshData, 10000); // Refresh stock data every 10 seconds without page reload
            
            // Auto-refresh page after 1 minute
            setTimeout(() => {
                location.reload(); // Refresh the page after 1 minute (60,000 ms)
            }, 60000); // 60000 ms = 1 minute
        });

        function refreshData() {
            fetch('update_prices.php')
                .then(() => fetchStocks())
                .catch(console.error);
        }

        function fetchStocks() {
            fetch('get_stocks.php')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('stock-table');
                    data.forEach(bank => {
                        const existingRow = document.querySelector(`#row-${bank.id}`);
                        
                        // If the row exists, update only the price
                        if (existingRow) {
                            existingRow.querySelector('.price').innerHTML = `$${parseFloat(bank.price).toFixed(2)}`;
                        } else {
                            // Otherwise, create a new row
                            const tr = document.createElement('tr');
                            tr.id = `row-${bank.id}`;
                            tr.innerHTML = ` 
                                <td>${bank.name}</td>
                                <td>${bank.symbol}</td>
                                <td class="price"><strong>$${parseFloat(bank.price).toFixed(2)}</strong></td>
                                <td><button onclick="loadChart(${bank.id}, '${bank.name.replace(/'/g, "\\'")}')">View</button></td>
                            `;
                            tbody.appendChild(tr);
                        }
                    });
                })
                .catch(console.error);
        }

        function loadChart(bankId, bankName) {
            fetch(`get_chart_data.php?bank_id=${bankId}`)
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(d => new Date(d.recorded_at).toLocaleTimeString());
                    const prices = data.map(d => parseFloat(d.price));

                    const ctx = document.getElementById('chart').getContext('2d');
                    if (chart) {
                        chart.data.labels = labels;
                        chart.data.datasets[0].data = prices;
                        chart.update();
                    } else {
                        chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: `${bankName} - Last 7 Records`,
                                    data: prices,
                                    borderColor: '#007BFF',
                                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        title: {
                                            display: true,
                                            text: 'Price ($)'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Time'
                                        }
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(console.error);
        }

        function filterTable() {
            const searchValue = document.getElementById('searchBox').value.toLowerCase();
            const rows = document.querySelectorAll('#stock-table tr');
            
            rows.forEach(row => {
                const bankName = row.cells[0].textContent.toLowerCase();
                const bankSymbol = row.cells[1].textContent.toLowerCase();
                if (bankName.indexOf(searchValue) !== -1 || bankSymbol.indexOf(searchValue) !== -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
