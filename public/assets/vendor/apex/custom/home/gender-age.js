var options = {
  chart: {
    type: "line",
    height: 300,
    toolbar: {
      show: false
    }
  },
  series: [
    {
      name: "High-Risk Pregnancies",
      data: [45, 60, 50, 70, 55, 65], // Sample data for Jan to Jun
    }
  ],
  xaxis: {
    categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    title: {
      text: "Month"
    }
  },
  yaxis: {
    title: {
      text: "Number of Cases"
    },
    min: 0,
  },
  stroke: {
    width: 3,
    curve: 'smooth'
  },
  markers: {
    size: 5,
    colors: ["#116AEF"],
    strokeColor: "#ffffff",
    strokeWidth: 2,
  },
  colors: ["#116AEF"],
  dataLabels: {
    enabled: true
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + " cases";
      }
    }
  }
};

var chart = new ApexCharts(document.querySelector("#genderAge"), options);
chart.render();
