<!DOCTYPE html>
<meta charset="utf-8">
<html>
<head>
<title>connection logger</title>
<style>
    body {
        width: 80%;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
  background:#444;
  color:#ddd;
    }
</style>

<script src="https://d3js.org/d3.v4.js"></script>
</head>
<body>
<h1>internet connection logger</h1>
<p>This site displays the status of our network.<br>
The logging is done by pinging 127.0.0.1 (local), 1.1.1.1 (DNS) and 8.8.8.8 (Google) 5 times every 5 minutes.<br>
The displayed information includes percent of packets lost and latency in ms.</p>

<p id="total_down" style="color:#f33; font-weight:bold;"></p>

<h2>downtime (% packets lost)</h2>
<div id="downtime"></div>

<h2>latency</h2>
<div id="latency"></div>


<script>

// parse the date / time
var parseTime = d3.timeParse("%d-%m-%Y_%H:%M:%S");

//##### downtime plot setup #####

// set the dimensions and margins of the graph
var margin = {top: 10, right: 30, bottom: 30, left: 60},
    width1 = 800 - margin.left - margin.right,
    height1 = 300 - margin.top - margin.bottom;

// set the ranges
var x = d3.scaleTime().range([0, width1]);
var y = d3.scaleLinear().range([height1, 0]);

// define the 1st line
var valueline = d3.line()
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.local_err); });

// define the 2nd line
var valueline2 = d3.line()
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.dns_err); });

// define the 3rd line
var valueline3 = d3.line()
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.google_err); });

// append the svg obgect to the the page
var svg1 = d3.select("#downtime")
  .append("svg")
    .attr("width", width1 + margin.left + margin.right)
    .attr("height", height1 + margin.top + margin.bottom)
  .append("g")
    .attr("transform",
          "translate(" + margin.left + "," + margin.top + ")");



//##### latency plot setup #####

// set the dimensions and margins of the graph
var margin = {top: 10, right: 30, bottom: 30, left: 60},
    width2 = 800 - margin.left - margin.right,
    height2 = 400 - margin.top - margin.bottom;

// set the ranges
var xx = d3.scaleTime().range([0, width2]);
var yy = d3.scaleLinear().range([height2, 0]);

// define the 1st line
var latencyline = d3.line()
    .x(function(d) { return xx(d.date); })
    .y(function(d) { return yy(d.local_ms); });

// define the 2nd line
var latencyline2 = d3.line()
    .x(function(d) { return xx(d.date); })
    .y(function(d) { return yy(d.dns_ms); });

// define the 3rd line
var latencyline3 = d3.line()
    .x(function(d) { return xx(d.date); })
    .y(function(d) { return yy(d.google_ms); });

// append the svg obgect to the the page
var svg2 = d3.select("#latency")
  .append("svg")
    .attr("width", width2 + margin.left + margin.right)
    .attr("height", height2 + margin.top + margin.bottom)
  .append("g")
    .attr("transform",
          "translate(" + margin.left + "," + margin.top + ")");



//##### Get the data #####

d3.csv("ping_log/ping_log.csv", function(error, data) {
  if (error) throw error;

  // format the data
  data.forEach(function(d) {
      d.date = parseTime(d.date);
      d.local_err = +d.local_err;
      d.local_ms = +d.local_ms;
      d.dns_err = +d.dns_err;
      d.dns_ms = +d.dns_ms;
      d.google_err = +d.google_err;
      d.google_ms = +d.google_ms;
  });


//### output total downtime ###
//array: data
 var downtime =  d3.sum(data, d => d.google_err>99);
 document.getElementById("total_down").innerHTML = "The total downtime is "+(downtime*5).toString()+"min.";


//##### plot downtime #####

  // Scale the range of the data
  x.domain(d3.extent(data, function(d) { return d.date; }));
  y.domain([0,100]);

  // Add the valueline path.
  svg1.append("path")
      .data([data])
      .attr("fill", "none")
      .attr("class", "line")
      .style("stroke", "red")
      .attr("d", valueline);

  // Add the valueline2 path.
  svg1.append("path")
      .data([data])
      .attr("fill", "none")
      .attr("class", "line")
      .style("stroke", "green")
      .attr("d", valueline2);

  // Add the valueline3 path.
  svg1.append("path")
      .data([data])
      .attr("fill", "none")
      .attr("class", "line")
      .style("stroke", "blue")
      .attr("d", valueline3);

  // Add the X Axis
  svg1.append("g")
      .attr("transform", "translate(0," + height1 + ")")
      .call(d3.axisBottom(x));

  // Add the Y Axis
  svg1.append("g")
      .call(d3.axisLeft(y));

  //legend
  svg1.append("circle").attr("cx",600).attr("cy",30).attr("r", 5).style("fill", "red")
  svg1.append("circle").attr("cx",600).attr("cy",60).attr("r", 5).style("fill", "green")
  svg1.append("circle").attr("cx",600).attr("cy",90).attr("r", 5).style("fill", "blue")
  svg1.append("text").attr("x", 620).attr("y", 30).text("192.168.0.1").style("font-size", "12px").attr("alignment-baseline","middle")
  svg1.append("text").attr("x", 620).attr("y", 60).text("1.1.1.1").style("font-size", "12px").attr("alignment-baseline","middle")
  svg1.append("text").attr("x", 620).attr("y", 90).text("8.8.8.8").style("font-size", "12px").attr("alignment-baseline","middle")


//##### plot latency #####

  // Scale the range of the data
  xx.domain(d3.extent(data, function(d) { return d.date; }));
  yy.domain([0, d3.max(data, function(d) {
    return Math.max(d.local_ms, d.dns_ms, d.google_ms); })]);

  // Add the valueline path.
  svg2.append("path")
      .data([data])
      .attr("class", "line")
      .style("stroke", "red")
      .attr("fill", "none")
      .attr("d", latencyline);

  // Add the valueline2 path.
  svg2.append("path")
      .data([data])
      .attr("class", "line")
      .style("stroke", "green")
      .attr("fill", "none")
      .attr("d", latencyline2);

  // Add the valueline3 path.
  svg2.append("path")
      .data([data])
      .attr("class", "line")
      .style("stroke", "blue")
      .attr("fill", "none")
      .attr("d", latencyline3);

  // Add the X Axis
  svg2.append("g")
      .attr("transform", "translate(0," + height2 + ")")
      .call(d3.axisBottom(xx));

  // Add the Y Axis
  svg2.append("g")
      .call(d3.axisLeft(yy));

  //legend
  svg2.append("circle").attr("cx",600).attr("cy",30).attr("r", 5).style("fill", "red")
  svg2.append("circle").attr("cx",600).attr("cy",60).attr("r", 5).style("fill", "green")
  svg2.append("circle").attr("cx",600).attr("cy",90).attr("r", 5).style("fill", "blue")
  svg2.append("text").attr("x", 620).attr("y", 30).text("192.168.0.1").style("font-size", "12px").attr("alignment-baseline","middle")
  svg2.append("text").attr("x", 620).attr("y", 60).text("1.1.1.1").style("font-size", "12px").attr("alignment-baseline","middle")
  svg2.append("text").attr("x", 620).attr("y", 90).text("8.8.8.8").style("font-size", "12px").attr("alignment-baseline","middle")

});

</script>


</body>
</html>

