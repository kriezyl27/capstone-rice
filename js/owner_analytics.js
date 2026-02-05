// ../js/owner_analytics.js
(function () {
  "use strict";

  function optLegend(showLegend = true) {
    return {
      plugins: { legend: { display: showLegend } },
      scales: { y: { beginAtZero: true } }
    };
  }

  function chartLine(el, labels, dataset, showLegend = false) {
    if (!el) return;
    new Chart(el, {
      type: "line",
      data: { labels, datasets: [dataset] },
      options: optLegend(showLegend)
    });
  }

  function chartBar(el, labels, datasets, showLegend = true) {
    if (!el) return;
    new Chart(el, {
      type: "bar",
      data: { labels, datasets },
      options: optLegend(showLegend)
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    const d = window.OWNER_ANALYTICS_DATA;
    if (!d) return;

    // Kg trend
    chartLine(
      document.getElementById("kgChart"),
      d.months,
      { label: "Kg Sold", data: d.kgData, tension: 0.35, fill: false },
      false
    );

    // Revenue trend
    chartBar(
      document.getElementById("revChart"),
      d.months,
      [{ label: "Revenue", data: d.revData }],
      false
    );

    // Forecast (kg + revenue)
    chartBar(
      document.getElementById("forecastChart"),
      d.forecastLabels,
      [
        { label: "Forecast kg", data: d.forecastKg },
        { label: "Forecast revenue", data: d.forecastRev }
      ],
      true
    );

    // Day of week
    chartBar(
      document.getElementById("dowChart"),
      d.daySalesLabels,
      [{ label: "Total Sold (kg)", data: d.daySalesData }],
      true
    );
  });
})();
