<!DOCTYPE html>
<html>
<head>
	<!--<meta http-equiv="refresh" content="10">-->
	<!-- Better refresh tip http://www.d3noob.org/2013/02/update-d3js-data-dynamically-button.html
						and http://www.d3noob.org/2013/02/update-d3js-data-dynamically.html 
						
					   Read:https://leanpub.com/D3-Tips-and-Tricks/read#leanpub-auto-update-data-dynamically---on-click		-->
					   
					   
	<title>Welcome Littos thermal heaven!</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body {
        max-width: 35em;
        /*margin: 0 auto;*/
        font-family: Tahoma, Verdana, Arial, sans-serif;
        font: 12px Arial;
    }
    
    
	.axis--x path {
		display: none;
	}

	.line {
		fill: none;
		stroke: steelblue;
		stroke-width: 1.5px;
	}
		
	.sp_line {
		fill: none;
		stroke: tomato;
		stroke-width: 1.5px;
	}
		
	.mv_line {
		fill: none;
		/*stroke: steelblue;*/
		stroke: green;
		stroke-width: 2.5px;
	}
		
	.op_line {
		fill: none;
		stroke: grey;
		stroke-width: 1px;
		stroke-dasharray: 6 2 3 2 6 2;
	}	
	
	.grid line {
		stroke: lightgrey;
		stroke-opacity: 0.7;
		shape-rendering: crispEdges;
	}

	.grid path {
		stroke-width: 0;
	}


</style>
</head>

<body>
	
	
<svg width="960" height="500"></svg>
<script src="/java/d3/d3.min.js"></script>
<script> //PID graph
var svg = d3.select("svg"),
    margin = {top: 20, right: 50, bottom: 50, left: 50},
    width = +svg.attr("width") - margin.left - margin.right,
    height = +svg.attr("height") - margin.top - margin.bottom,
    g = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var parseTime = d3.timeParse("%Y-%m-%d %H:%M:%S");

var x = d3.scaleTime().range([0, width]);		// Time scale
var y = d3.scaleLinear().range([height, 0]);	//Temperature scale
var y_op = d3.scaleLinear().range([height, 0]);	//Output scale

var sp_valueline = d3.line()
    .x(function(d) { return x(d.time); })	//Time stamp
    .y(function(d) { return y(d.sp); });	//Set Point   y value scaled to Temperature ,(y), scale
    
var mv_valueline = d3.line()
    .x(function(d) { return x(d.time); })	//Time stamp
    .y(function(d) { return y(d.mv); });	//Measured value (temperature)   y value scaled to Temperature ,(y), scale
    
var op_valueline = d3.line()
    .x(function(d) { return x(d.time); })	//Time stamp
    .y(function(d) { return y_op(d.op); });	//PID controller output, 0-100%  y value scaled to Output ,(y_op), scale     

var x_axis = d3.axisBottom(x)
	.ticks(10);

var temp_axis = d3.axisRight(y)
	.ticks(10);
	
var op_axis = d3.axisLeft(y_op)
	.ticks(10);
	
// gridlines in x axis function
function make_x_gridlines() {		
    return d3.axisBottom(x)
        .ticks(10)
}

// gridlines in y axis function
function make_y_gridlines() {		
    return d3.axisLeft(y)
        .ticks(10)
}	
    
// Get the data
// The first callback function is executed for all values, 
//the second function is executed after all values have been loaded
d3.csv("/testtemp", function(d) {
  d.time = parseTime(d.time);
  d.sp = +d.sp;
  d.mv = +d.mv;
  d.op = +d.op;
  return d;
}, function(error, data) {
  if (error) throw error;
console.log(data);

  x.domain(d3.extent(data, function(d) { return d.time; }));
  //y.domain(d3.extent(data, function(d) { return Math.max(d.mv, d.sp); }));		//###The maximum value is the greater of Sp or Mv!
  //y.domain[0] = d3.min(data, function(d) { return Math.min(d.mv, d.sp); }); //Lower y domain limit is lesser of min sp and min mv
  //y.domain[1] = d3.max(data, function(d) { return Math.max(d.mv, d.sp); });//U pery domain limit is higher of max sp and max mv
  //console.log(d3.min(data, function(d) { return Math.min(d.mv, d.sp); }));
  //console.log(d3.max(data, function(d) { return Math.max(d.mv, d.sp); }));
  var ymin = d3.min(data, function(d) { return Math.min(d.mv, d.sp); }); //Lower y domain limit is lesser of min sp and min mv
  var ymax = d3.max(data, function(d) { return Math.max(d.mv, d.sp); }); //Upper y domain  limit is higher of max sp and max mv
  y.domain([ymin*0.975,ymax*1.025]);

  y_op.domain([0,100]);	//The output domain is 0-100% 
  
  // add the X gridlines
  g.append("g")			
      .attr("class", "grid")
      .attr("transform", "translate(0," + height + ")")
      .call(make_x_gridlines()
          .tickSize(-height)
          .tickFormat("")
      )

  // add the Y gridlines
  g.append("g")			
      .attr("class", "grid")
      .call(make_y_gridlines()
          .tickSize(-width)
          .tickFormat("")
      )
  
  // add x axis
  g.append("g")
      .attr("class", "axis axis--x")
      .attr("transform", "translate(0," + height + ")")
      .call(x_axis);
  
  // add y axis for temperature
  g.append("g")
      .attr("class", "axis axis--y")
      .attr("stroke", "steelblue")
      .attr("transform", "translate(" + width + ",0)")
      .call(temp_axis)
    .append("text")
      .attr("fill", "#000")
      //.attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", "0.71em")
      .style("text-anchor", "end")
      .attr("transform", "translate(35," + (height - 20) + ")")
      .text("deg C");  
  
  // add value lines for set point and measured temp
  g.append("path")
      .datum(data)
      .attr("class", "sp_line")
      .attr("d", sp_valueline);
      
  g.append("path")
      .datum(data)
      .attr("class", "mv_line")
      .attr("d", mv_valueline);
  
  // add y axis for output value
  g.append("g")
      .attr("class", "axis axis--y")
      .attr("stroke", "grey")
      .call(op_axis)
    .append("text")
      .attr("fill", "#000")
      //.attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", "0.71em")
      .style("text-anchor", "end")
      .attr("transform", "translate(-10," + (height - 20) + ")")
      .text("output");
  
  // add value line for output value    
  g.append("path")
      .datum(data)
      .attr("class", "op_line")
      .attr("d", op_valueline);
      
  // Add numerials for MV C (SP C)  and OP %
  g.append("text")
      .attr("fill", "green")
      //.attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", "0.71em")
      .style("text-anchor", "end")
      .attr("transform", "translate(250," + (height +25) + ")")
      .attr("font-size", "150%")
      .text(data[data.length-1].mv + " C" );
      
  g.append("text")
      .attr("fill", "tomato")
      //.attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", "0.71em")
      .style("text-anchor", "end")
      .attr("transform", "translate(350," + (height +25) + ")")
      .attr("font-size", "150%")
      .text("(" + data[data.length-1].sp + " C )");
      
  g.append("text")
      .attr("fill", "grey")
      //.attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", "0.71em")
      .style("text-anchor", "end")
      .attr("transform", "translate(410," + (height +25) + ")")
      .attr("font-size", "150%")
      .text(data[data.length-1].op + "%");               
});


//Update at an interval, expressed in ms
var inter = setInterval(function() {
                updateData();
        }, 10000);

//Update data
function updateData() {

   // Get the data again
    // The first callback function is executed for all values, 
	//the second function is executed after all values have been loaded
	d3.csv("/testtemp", function(d) {
	  d.time = parseTime(d.time);
	  d.sp = +d.sp;
	  d.mv = +d.mv;
	  d.op = +d.op;
	  return d;
	}, function(error, data) {
	  if (error) throw error;
	console.log(data);
	
	//Redo the domain calculations as data may be out of bounds	
	  x.domain(d3.extent(data, function(d) { return d.time; }));
	  var ymin = d3.min(data, function(d) { return Math.min(d.mv, d.sp); }); //Lower y domain limit is lesser of min sp and min mv
	  var ymax = d3.max(data, function(d) { return Math.max(d.mv, d.sp); }); //Upper y domain  limit is higher of max sp and max mv
	  y.domain([ymin*0.975,ymax*1.025]);
	
	  y_op.domain([0,100]);	//The output domain is 0-100% 
	  
	//Select what to apply the changes to
	  var svg = d3.select("body").transition();
	  
	//Make the updates
	svg.select(".sp_line")   // change the set point line
            .duration(50)
            .attr("d", sp_valueline);
    svg.select(".mv_line")   // change the measured value line
            .duration(50)
            .attr("d", mv_valueline);
	svg.select(".op_line")   // change the output value line
            .duration(50)
            .attr("d", op_valueline);
            
    svg.select(".x.axis") // change the x axis
            .duration(50)
            .call(x_axis);
    svg.select(".x.axis") // change the temp axis
            .duration(50)
            .call(temp_axis);
    // output axis will not change, so lets skip that item
    
    //Update numeric instant values under the graph too	  
	  });
}


</script>
<p>
<p>

	<h2>Controller settings</h2><br>
	
	<?php 
	$datafil = fopen("data_to_cnt", "r") or die("Unable to open file!");
	$mode = rtrim(fgets($datafil));
	$SP = rtrim(fgets($datafil));
	$OP = rtrim(fgets($datafil));
	$P = rtrim(fgets($datafil));
	$I = rtrim(fgets($datafil));
	$D = rtrim(fgets($datafil));
	$exec_int = rtrim(fgets($datafil));
	$log_int = rtrim(fgets($datafil));
	fclose($datafil);
	?>
<form action="set_data.php" method="post">
 	<input type="radio" name="mode" value="off" <?php if ($mode=='off') {echo ' checked'; } ?>>Off<br>
 	<input type="radio" name="mode" value="man" <?php if ($mode=='man') {echo ' checked'; } ?>>Man<br>
 	<input type="radio" name="mode" value="aut" <?php if ($mode=='aut') {echo ' checked'; } ?>>Auto<br>
		
	Set point: <input type="text" name="SP" value=<?php echo $SP ?>>C<br>
	Output:    <input type="text" name="OP" value=<?php echo $OP ?>>%<br>
	<br>
	
	P:         <input type="text" name="P" value=<?php echo $P ?>><br>
	I:         <input type="text" name="I" value=<?php echo $I ?>>s<br>
	D:         <input type="text" name="D" value=<?php echo $D ?>>s<br>
	<br>
	Exe int.:  <input type="text" name="exec_int" value=<?php echo $exec_int ?>>ms<br>
	Log int.:  <input type="text" name="log_int" value=<?php echo $log_int ?>>min<br>
	<br>
	<input type="submit"
		   value="Set">
</form>	

<p>For online documentation of nginx and support please refer to
<a href="http://nginx.org/">nginx.org</a>.<br/>

<p><em>Thank you for using nginx.</em></p>
</body>
</html>
