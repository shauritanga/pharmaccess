var options = {
  chart: {
    height: 350,
    type: "line",
    toolbar: {
      show: false
    }
  },
  dataLabels: {
    enabled: false
  },
  fill: {
    type: 'solid',
    opacity: [1, 1, 0.4]
  },
  stroke: {
    curve: "smooth",
    width: [0, 0, 3] // Bars for meds, line for forecast
  },
  series: [
    {
      name: 'Paracetamol',
      type: 'bar',
      data: [300, 350, 400, 380, 420, 390, 450, 470, 500, 520, 540, 560]
    },
    {
      name: 'Amoxicillin',
      type: 'bar',
      data: [200, 220, 250, 230, 260, 240, 270, 280, 290, 300, 310, 320]
    },
    {
      name: 'Insulin (Forecast)',
      type: 'line',
      data: [100, 120, 130, 150, 160, 180, 200, 210, 230, 250, 260, 270]
    }
  ],
  xaxis: {
    categories: [
      "Jan", "Feb", "Mar", "Apr", "May", "Jun",
      "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
    ],
    title: {
      text: "Month"
    }
  },
  yaxis: {
    title: {
      text: "Demand Quantity"
    },
    labels: {
      show: true
    }
  },
  colors: ["#116AEF", "#32C2F2", "#FF5733"],
  legend: {
    position: 'bottom',
    horizontalAlign: 'center'
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + " units";
      }
    }
  },
  grid: {
    borderColor: "#d8dee6",
    strokeDashArray: 5,
    xaxis: {
      lines: { show: true }
    },
    yaxis: {
      lines: { show: true }
    },
    padding: {
      top: 0, right: 0, bottom: 0, left: 0
    }
  }
};

var chart = new ApexCharts(document.querySelector("#treatment"), options);
chart.render();

// Store chart instance globally for dynamic updates
window.medicationTrendsChart = chart;
