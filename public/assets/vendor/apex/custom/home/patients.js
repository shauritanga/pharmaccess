var options = {
  chart: {
    type: 'bar',
    height: 350,
    toolbar: {
      show: false
    }
  },
  series: [{
    name: 'Cases',
    data: [120, 95, 75, 60, 45] // number of cases per disease
  }],
  xaxis: {
    categories: ['Malaria', 'Typhoid', 'Cholera', 'Dengue', 'Flu'], // disease names
    title: {
      text: 'Disease Name'
    }
  },
  yaxis: {
    title: {
      text: 'Number of Cases'
    }
  },
  colors: ['#116AEF'],
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '50%',
      borderRadius: 5
    }
  },
  dataLabels: {
    enabled: true
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + " cases";
      }
    }
  },
  legend: {
    show: false
  }
};

var chart = new ApexCharts(document.querySelector("#topDiseases"), options);
chart.render();
