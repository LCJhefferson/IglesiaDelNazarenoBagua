/**
 * ARCHIVO: inicioAdmin.js
 * Control dinámico de los gráficos usando Chart.js
 */

document.addEventListener("DOMContentLoaded", function() {
    
    // =========================================================================
    // PARSEO DE DATOS DESDE EL HTML (INYECTADOS POR PHP)
    // =========================================================================
    const chartsContainer = document.getElementById('dashboard-charts-data');
    if (!chartsContainer) return; 

    const cargosLabels = JSON.parse(chartsContainer.getAttribute('data-cargos-labels') || '[]');
    const cargosData = JSON.parse(chartsContainer.getAttribute('data-cargos-data') || '[]');
    
    const condicionLabels = JSON.parse(chartsContainer.getAttribute('data-condicion-labels') || '[]');
    const condicionData = JSON.parse(chartsContainer.getAttribute('data-condicion-data') || '[]');
    
    const visitasLabels = JSON.parse(chartsContainer.getAttribute('data-visitas-labels') || '[]');
    const visitasData = JSON.parse(chartsContainer.getAttribute('data-visitas-data') || '[]');

    const motivosLabels = JSON.parse(chartsContainer.getAttribute('data-motivos-labels') || '[]');
    const motivosData = JSON.parse(chartsContainer.getAttribute('data-motivos-data') || '[]');

    // =========================================================================
    // 1. GRÁFICO DE CARGOS (Dona)
    // =========================================================================
    const ctxCargos = document.getElementById('chartCargos');
    if (ctxCargos && cargosData.length > 0) {
        new Chart(ctxCargos, {
            type: 'doughnut',
            data: {
                labels: cargosLabels,
                datasets: [{
                    data: cargosData,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '60%' }
        });
    }

    // =========================================================================
    // 2. NUEVO: GRÁFICO DE MOTIVOS DE VISITA (Dona)
    // =========================================================================
    const ctxMotivos = document.getElementById('chartMotivos');
    if (ctxMotivos && motivosData.length > 0) {
        new Chart(ctxMotivos, {
            type: 'doughnut',
            data: {
                labels: motivosLabels,
                datasets: [{
                    data: motivosData,
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#f43f5e'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '60%' }
        });
    }

    // =========================================================================
    // 3. GRÁFICO DE CONDICIONES DE SALUD (Dona)
    // =========================================================================
    const ctxCondiciones = document.getElementById('chartCondiciones');
    if (ctxCondiciones && condicionData.length > 0) {
        new Chart(ctxCondiciones, {
            type: 'doughnut',
            data: {
                labels: condicionLabels,
                datasets: [{
                    data: condicionData,
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'], 
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '60%' }
        });
    }

    // =========================================================================
    // 4. GRÁFICO DE BARRAS EVOLUTIVO (Visitas)
    // =========================================================================
    const ctxBarras = document.getElementById('chartVisitasBarras');
    if (ctxBarras && visitasData.length > 0) {
        new Chart(ctxBarras, {
            type: 'bar',
            data: {
                labels: visitasLabels,
                datasets: [{
                    label: 'Visitas Efectuadas',
                    data: visitasData,
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: '#64748b' }, grid: { borderDash: [4, 4], color: '#e2e8f0' } },
                    x: { ticks: { color: '#64748b' }, grid: { display: false } }
                }
            }
        });
    }
});