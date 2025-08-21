// Disease Distribution Heatmap for Zanzibar Districts
var options = {
  chart: {
    height: 450,
    type: "heatmap",
    toolbar: {
      show: false,
    },
  },
  dataLabels: {
    enabled: true,
    style: {
      colors: ['#fff'],
      fontSize: '12px',
      fontWeight: 'bold'
    }
  },
  series: [
    {
      name: "Malaria",
      data: [
        { x: "Urban West", y: 45 },
        { x: "Chake Chake", y: 38 },
        { x: "Mkoani", y: 32 },
        { x: "Wete", y: 28 },
        { x: "Micheweni", y: 25 },
        { x: "Konde", y: 22 },
        { x: "Chakechake", y: 20 },
        { x: "Mkoani Rural", y: 18 },
        { x: "Wete Rural", y: 15 },
        { x: "Micheweni Rural", y: 12 },
        { x: "Konde Rural", y: 10 }
      ]
    },
    {
      name: "Typhoid",
      data: [
        { x: "Urban West", y: 35 },
        { x: "Chake Chake", y: 30 },
        { x: "Mkoani", y: 25 },
        { x: "Wete", y: 22 },
        { x: "Micheweni", y: 20 },
        { x: "Konde", y: 18 },
        { x: "Chakechake", y: 15 },
        { x: "Mkoani Rural", y: 12 },
        { x: "Wete Rural", y: 10 },
        { x: "Micheweni Rural", y: 8 },
        { x: "Konde Rural", y: 6 }
      ]
    },
    {
      name: "Dengue",
      data: [
        { x: "Urban West", y: 25 },
        { x: "Chake Chake", y: 22 },
        { x: "Mkoani", y: 18 },
        { x: "Wete", y: 15 },
        { x: "Micheweni", y: 12 },
        { x: "Konde", y: 10 },
        { x: "Chakechake", y: 8 },
        { x: "Mkoani Rural", y: 6 },
        { x: "Wete Rural", y: 5 },
        { x: "Micheweni Rural", y: 4 },
        { x: "Konde Rural", y: 3 }
      ]
    },
    {
      name: "Cholera",
      data: [
        { x: "Urban West", y: 15 },
        { x: "Chake Chake", y: 12 },
        { x: "Mkoani", y: 10 },
        { x: "Wete", y: 8 },
        { x: "Micheweni", y: 6 },
        { x: "Konde", y: 5 },
        { x: "Chakechake", y: 4 },
        { x: "Mkoani Rural", y: 3 },
        { x: "Wete Rural", y: 2 },
        { x: "Micheweni Rural", y: 2 },
        { x: "Konde Rural", y: 1 }
      ]
    },
    {
      name: "Asthma",
      data: [
        { x: "Urban West", y: 20 },
        { x: "Chake Chake", y: 18 },
        { x: "Mkoani", y: 15 },
        { x: "Wete", y: 12 },
        { x: "Micheweni", y: 10 },
        { x: "Konde", y: 8 },
        { x: "Chakechake", y: 6 },
        { x: "Mkoani Rural", y: 5 },
        { x: "Wete Rural", y: 4 },
        { x: "Micheweni Rural", y: 3 },
        { x: "Konde Rural", y: 2 }
      ]
    }
  ],
  colors: ["#FF4560", "#008FFB", "#00E396", "#775DD0", "#FEB019"],
  xaxis: {
    type: "category",
    categories: [
      "Urban West", "Chake Chake", "Mkoani", "Wete", "Micheweni",
      "Konde", "Chakechake", "Mkoani Rural", "Wete Rural",
      "Micheweni Rural", "Konde Rural"
    ],
    labels: {
      style: {
        fontSize: '11px'
      }
    }
  },
  yaxis: {
    labels: {
      style: {
        fontSize: '12px'
      }
    }
  },
  grid: {
    padding: {
      right: 20
    }
  },
  legend: {
    position: 'top',
    horizontalAlign: 'center'
  },
  tooltip: {
    y: {
      formatter: function(val, opts) {
        return val + " cases";
      }
    }
  },
  plotOptions: {
    heatmap: {
      shadeIntensity: 0.5,
      radius: 0,
      useFillColorAsStroke: true,
      colorScale: {
        ranges: [
          { from: 0, to: 5, name: "Very Low", color: "#E3F2FD" },
          { from: 6, to: 15, name: "Low", color: "#90CAF9" },
          { from: 16, to: 25, name: "Medium", color: "#42A5F5" },
          { from: 26, to: 35, name: "High", color: "#1E88E5" },
          { from: 36, to: 50, name: "Very High", color: "#0D47A1" }
        ]
      }
    }
  }
};

var chart = new ApexCharts(document.querySelector("#diseaseHeatmap"), options);
chart.render();

// Store chart instance globally for dynamic updates
window.diseaseHeatmapChart = chart;