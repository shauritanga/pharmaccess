// Patient Age Distribution Chart
var options = {
  chart: {
    height: 350,
    type: "donut",
    toolbar: {
      show: false,
    },
  },
  dataLabels: {
    enabled: true,
    style: {
      fontSize: '12px',
      fontWeight: 'bold'
    },
    formatter: function (val, opts) {
      return opts.w.config.series[opts.seriesIndex] + " patients";
    }
  },
  series: [45, 89, 156, 234, 178, 123, 67],
  labels: [
    "0-10 years",
    "11-20 years", 
    "21-30 years",
    "31-40 years",
    "41-50 years",
    "51-60 years",
    "60+ years"
  ],
  colors: [
    "#FF6B6B", // Red for children
    "#4ECDC4", // Teal for teens
    "#45B7D1", // Blue for young adults
    "#96CEB4", // Green for adults
    "#FFEAA7", // Yellow for middle-aged
    "#DDA0DD", // Purple for seniors
    "#98D8C8"  // Light green for elderly
  ],
  legend: {
    position: 'bottom',
    horizontalAlign: 'center',
    fontSize: '12px',
    markers: {
      width: 8,
      height: 8,
    }
  },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show: true,
          name: {
            show: true,
            fontSize: '14px',
            fontWeight: 'bold',
            color: '#373d3f'
          },
          value: {
            show: true,
            fontSize: '16px',
            fontWeight: 'bold',
            color: '#373d3f',
            formatter: function (val) {
              return val + " patients";
            }
          },
          total: {
            show: true,
            label: 'Total Patients',
            fontSize: '14px',
            fontWeight: 'bold',
            color: '#373d3f',
            formatter: function (w) {
              const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
              return total + " patients";
            }
          }
        }
      }
    }
  },
  tooltip: {
    y: {
      formatter: function(val, opts) {
        const total = opts.globals.seriesTotals.reduce((a, b) => a + b, 0);
        const percentage = ((val / total) * 100).toFixed(1);
        return val + " patients (" + percentage + "%)";
      }
    }
  },
  responsive: [
    {
      breakpoint: 768,
      options: {
        chart: {
          height: 300
        },
        legend: {
          position: 'bottom'
        }
      }
    }
  ]
};

var chart = new ApexCharts(document.querySelector("#ageDistribution"), options);
chart.render();

// Store chart instance globally for dynamic updates
window.ageDistributionChart = chart;
