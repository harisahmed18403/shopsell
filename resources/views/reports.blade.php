<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Sales Reports</h1>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300 mb-6">
        <div class="card-body p-4">
            <h2 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Monthly Sales Revenue</h2>
            <div style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart').getContext('2d');
            const data = @json($monthlyData);
            
            const labels = data.map(item => item.month);
            const values = data.map(item => item.total);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (£)',
                        data: values,
                        backgroundColor: '#1a73e8',
                        borderColor: '#1a73e8',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
