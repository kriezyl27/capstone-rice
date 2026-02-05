// ../js/admin_analytics.js
(function () {
  "use strict";

  // tiny helper so you don't repeat chart options
  function baseOptions() {
    return {
      plugins: { legend: { display: true } },
      scales: { y: { beginAtZero: true } }
    };
  }

  function renderSalesChart(el, labels, actual, forecast) {
    if (!el) return;

    new Chart(el, {
      type: "line",
      data: {
        labels,
        datasets: [
          { label: "Actual", data: actual, tension: 0.4, fill: false },
          { label: "Forecast (SMA)", data: forecast, borderDash: [6, 6], tension: 0.4, fill: false }
        ]
      },
      options: baseOptions()
    });
  }

  function renderDowChart(el, labels, data) {
    if (!el) return;

    new Chart(el, {
      type: "bar",
      data: {
        labels,
        datasets: [{ label: "Total Sold (kg)", data }]
      },
      options: baseOptions()
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    // data injected from PHP
    const d = window.ADMIN_ANALYTICS_DATA;
    if (!d) return;

    renderSalesChart(
      document.getElementById("salesChart"),
      d.combinedLabels,
      d.actualPadded,
      d.forecastPadded
    );

    renderDowChart(
      document.getElementById("dowChart"),
      d.daySalesLabels,
      d.daySalesData
    );
  });
})();
