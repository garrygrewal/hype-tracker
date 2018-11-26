<?php

    require_once('../private/initialize.php');

    $sneaker_id = $_GET['id'] ?? '';
    $sneaker_name = $_GET['name'] ?? '';

    $search = get_sneaker($sneaker_id, $sneaker_name);
    $sneaker = mysqli_fetch_assoc($search);
    mysqli_free_result($search);

    include(SHARED_PATH . '/public_header.php');
    include(SHARED_PATH . '/public_navigation.php');

?>
<div class="container">
    <div class="row my-5 justify-content-start">
        <div class="col-4">
            <?php echo '<img src="data:image/jpeg;base64,'.base64_encode( $sneaker['image'] ).'" class="img-thumbnail border border-dark"/>'; ?>
        </div>
        <div class="col-4">
            <h2 class="text-muted"><?php echo h($sneaker['brand_name']); ?></h2>
            <h1><?php echo h($sneaker['sneaker_name']); ?></h1>
            <p class="mt-3 text-muted">$ <?php echo h($sneaker['price']); ?></p>
        </div>
    </div>
    <div class="row mt-5 ml-2">
        <h3>Hype Trend</h3>
    </div>
    <div class="row ml-5">
        <svg></svg>
        <script src="https://d3js.org/d3.v5.min.js"></script>
        <script>

            var arr = [];

            <?php 
                $rankings = get_ranking($sneaker['sneaker_id']);
                while ($ranking = mysqli_fetch_assoc($rankings)) {
            ?>
                    parseData(<?php echo $ranking['month']; ?>, <?php echo $ranking['score']; ?>);
            <?php        
                }
            ?>

            drawChart(arr);

            function parseData(month, score) {
                var currMonth = Date.now();
                arr.push({
                    date: new Date(2018, month),
                    value: score
                })
            }

            function drawChart(data) {
                var svgWidth = 600, svgHeight = 400;
                var margin = { top: 20, right: 20, bottom: 30, left: 50 };
                var width = svgWidth - margin.left - margin.right;
                var height = svgHeight - margin.top - margin.bottom;
                var svg = d3.select('svg')
                    .attr("width", svgWidth)
                    .attr("height", svgHeight);

                var g = svg.append("g")
                    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
                
                var x = d3.scaleTime().rangeRound([0, width]);
                var y = d3.scaleLinear().rangeRound([height, 0]);

                var line = d3.line()
                    .x(function(d) { return x(d.date)})
                    .y(function(d) { return y(d.value)});
                x.domain(d3.extent(data, function(d) { return d.date }));
                y.domain(d3.extent(data, function(d) { return d.value }));

                g.append("g")
                    .attr("transform", "translate(0," + height + ")")
                    .call(d3.axisBottom(x))
                    .select(".domain")
                    .remove();

                g.append("g")
                    .call(d3.axisLeft(y))
                    .append("text")
                    .attr("fill", "#000")
                    .attr("transform", "rotate(-90)")
                    .attr("y", 6)
                    .attr("dy", "0.71em")
                    .attr("text-anchor", "end")
                    .text("Score (based on relative mentions)");
                
                g.append("path")
                    .datum(data)
                    .attr("fill", "none")
                    .attr("stroke", "steelblue")
                    .attr("stroke-linejoin", "round")
                    .attr("stroke-linecap", "round")
                    .attr("stroke-width", 1.5)
                    .attr("d", line);
            }
        </script>
    </div>
</div>