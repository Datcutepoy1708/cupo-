/**
 * =========================================================
 * CUPO ADMIN - FINANCIAL ANALYTICS & REVENUE REPORTS JS
 * =========================================================
 */

(function () {
    'use strict';

    // Revenue Trend Line Chart
    const trendCtx = document.getElementById('revenueTrendChart')?.getContext('2d');
    if (trendCtx && window.revenueTrendData) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: window.revenueTrendData.labels,
                datasets: [
                    {
                        label: 'Tổng GMV Hàng Hóa (VNĐ)',
                        data: window.revenueTrendData.gmv,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Doanh Thu Hoa Hồng Sàn (VNĐ)',
                        data: window.revenueTrendData.commission,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Roboto', sans-serif", size: 12 },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + ' Tr';
                                if (value >= 1000) return (value / 1000).toFixed(0) + ' K';
                                return value;
                            }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Category Share Donut Chart
    const catCtx = document.getElementById('categoryShareChart')?.getContext('2d');
    if (catCtx && window.categoryShareData) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: window.categoryShareData.labels,
                datasets: [{
                    data: window.categoryShareData.series,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            font: { family: "'Roboto', sans-serif", size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.label + ': ' + new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.raw);
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

})();
