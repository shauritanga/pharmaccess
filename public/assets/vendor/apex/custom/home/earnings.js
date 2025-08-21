var options = {
  chart: {
    type: 'scatter',
    height: 350,
    toolbar: {
      show: false,
    }
  },
  series: [{
    name: "Facility A",
    data: [
      [2, 70],  // [quality score, outcome %]
    ]
  }, {
    name: "Facility B",
    data: [
      [4, 85],
    ]
  }, {
    name: "Facility C",
    data: [
      [3, 60],
    ]
  }, {
    name: "Facility D",
    data: [
      [5, 90],
    ]
  }],
  xaxis: {
    title: {
      text: "Facility Quality (1=Low, 5=High)"
    },
    min: 1,
    max: 5,
    tickAmount: 4,
  },
  yaxis: {
    title: {
      text: "Disease Outcome (%)"
    },
    min: 0,
    max: 100,
  },
  markers: {
    size: 8,
    colors: ["#116aef", "#0ebb13", "#ff9900", "#e91e63"],
    strokeWidth: 2,
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + " %";
      }
    }
  }
};

var chart = new ApexCharts(document.querySelector("#facility"), options);
chart.render();

// Store chart instance globally for dynamic updates
window.facilityPerformanceChart = chart;
