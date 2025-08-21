var options = {
  chart: {
    height: 300,
    type: "bar",
    toolbar: {
      show: false,
    },
  },
  plotOptions: {
    bar: {
      columnWidth: "60%",
      borderRadius: 4,
      distributed: true,
    },
  },
  series: [
    {
      name: "Cases",
      data: [120, 95, 80, 60, 45, 30], // Sample data
    },
  ],
  legend: {
    show: false,
  },
  xaxis: {
    categories: [
      "Diabetes",
      "Hypertension",
      "Asthma",
      "Cancer",
      "HIV/AIDS",
      "Arthritis"
    ],
    labels: {
      rotate: -45,
      style: {
        fontSize: "12px"
      }
    },
    axisBorder: {
      show: true,
    },
  },
  yaxis: {
    title: {
      text: "Number of Cases"
    }
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + " cases";
      },
    },
  },
  colors: [
    "#116AEF", "#327FF2", "#5394F5", "#75AAF9", "#96BFFC", "#B7D4FF"
  ],
  grid: {
    borderColor: "#d8dee6",
    strokeDashArray: 5,
    xaxis: {
      lines: {
        show: false,
      },
    },
    yaxis: {
      lines: {
        show: true,
      },
    },
    padding: {
      top: 10,
      right: 20,
      bottom: 20,
    },
  },
};

var chart = new ApexCharts(document.querySelector("#claims"), options);
chart.render();

// Store chart instance globally for dynamic updates
window.chronicDiseasesChart = chart;
