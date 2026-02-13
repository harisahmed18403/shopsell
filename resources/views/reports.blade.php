<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sales Reports') }}
        </h2>
    </x-slot>

    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <h2 class="card-title mb-4">Monthly Sales Revenue</h2>
            <div style="height: 400px;">
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
