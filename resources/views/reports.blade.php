<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Sales Reports</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
            <div class="card-body p-4">
                <h2 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Monthly Revenue (Sell + Repair)</h2>
                <div style="height: 250px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
            <div class="card-body p-4">
                <h2 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Buy vs Sell Comparison</h2>
                <div style="height: 250px;">
                    <canvas id="buySellChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300 mt-6">
        <div class="card-body p-4">
            <h2 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Combined Profit (Revenue - Buy)</h2>
            <div style="height: 300px;">
                <canvas id="profitChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Revenue Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const salesData = @json($monthlyData);
            new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: salesData.map(item => item.month),
                    datasets: [{
                        label: 'Revenue (£)',
                        data: salesData.map(item => item.total),
                        backgroundColor: '#2563eb',
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Buy vs Sell Chart
            const bsCtx = document.getElementById('buySellChart').getContext('2d');
            const bsDataRaw = @json($buyVsSell);
            const months = [...new Set(bsDataRaw.map(item => item.month))];
            const buyData = months.map(m => {
                const found = bsDataRaw.find(d => d.month === m && d.type === 'buy');
                return found ? found.total : 0;
            });
            const sellData = months.map(m => {
                const found = bsDataRaw.find(d => d.month === m && d.type === 'sell');
                return found ? found.total : 0;
            });

            new Chart(bsCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        { label: 'Sell (£)', data: sellData, borderColor: '#2563eb', tension: 0.1 },
                        { label: 'Buy (£)', data: buyData, borderColor: '#dc2626', tension: 0.1 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Profit Chart
            const profitCtx = document.getElementById('profitChart').getContext('2d');
            const profitData = @json($profitData);
            new Chart(profitCtx, {
                type: 'bar',
                data: {
                    labels: profitData.map(item => item.month),
                    datasets: [{
                        label: 'Net Profit/Loss (£)',
                        data: profitData.map(item => item.profit),
                        backgroundColor: profitData.map(item => item.profit >= 0 ? '#16a34a' : '#dc2626'),
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
    </script>
</x-app-layout>
