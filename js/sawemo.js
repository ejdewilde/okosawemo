var width = 900,
    height = 160;

var svg1 = d3.select("#kopikonen")
    .append("svg")
    .attr("id", "ikonen")
    .attr("viewBox", "0 0 930 150")
    .attr("preserveAspectRatio", "xMinYMin meet")
    ;

var numberOfButtons = 9;

var x = d3.scale.linear()
    .domain([0, numberOfButtons])
    .range([0, width]);

//ikonen
var buttonGroup = svg1.selectAll("g")
    .data(ikonen)
    .enter().append("g")
    .attr("transform", function (d) { return "translate(" + d.x + "," + d.y + ")"; });

var grootte = 70;
var hoogte = 40;

buttonGroup.append("image")
    .attr("xlink:href", function (d, i) { return d.pad })
    .attr("x", 0)
    .attr("y", hoogte)
    .attr("width", grootte)
    .attr("height", grootte)
    .attr("cursor", "pointer")
    .on("mouseover", function (d, i) {
        d3.select(this).transition()
            .ease("elastic")
            .duration("500")
            .attr("x", -grootte / 2)
            .attr("y", hoogte - grootte / 2)
            .attr("width", grootte * 2)
            .attr("height", grootte * 2);
        LaatZien(i);
        select(i);

    })
    .on("mouseout", function (d, i) {
        d3.select(this).transition()
            .ease("quad")
            .delay("100")
            .duration("200")
            .attr("x", 0)
            .attr("y", hoogte)
            .attr("width", grootte)
            .attr("height", grootte);

    });



function select(toon) {
    render(databegin, toon);
}

var balkenfig = d3.select("#barcharts")
    .append("svg")
    .attr("id", "bars")
    .attr("viewBox", "0 0 1025 600")
    .attr("preserveAspectRatio", "xMinYMin meet")
    .attr("class", "svg-content")
    ;

/*window.addEventListener("resize", pasAan);
*/
function pasAan() {

    console.log(window.outerWidth);
    /*
      if (window.outerWidth < 800)
      {
          var balkenfig = balkenfig.attr("viewBox", "300 0 550 450");
          var svg1 = d3.select("#ikonen").attr("viewBox", "10 0 800 150");
      } else 
      { 
          var balkenfig = balkenfig.attr("viewBox", "0 0 850 500");
          var svg1 = d3.select("#ikonen").attr("viewBox", "0 0 900 150");
      }
      */
}


function render(data, category) {
    var xScale = d3.scale.linear().domain([1, 5]).range([0, 275]);

    var aantalbalken = 9;
    var balkhoogte = 50;
    var hoogte = aantalbalken * balkhoogte;
    var breedte = 300;
    var links = 725;
    //balken zelf:
    balkenfig
        .selectAll("rect")
        .data(data.filter(function (d) { return d.kop == category; }))
        .enter()
        .append("rect")
        .attr("height", balkhoogte)
        .attr("width", breedte + 25)
        .attr("rx", 5)
        .attr("ry", 5)
        .style("fill", "#c5e8ddff")
        .style("stroke", "red")
        .style("stroke-width", "0px")
        .style("opacity", 1)
        .attr("y", function (d, i) { return i * (balkhoogte + 5) + 35; })
        .attr("x", -25)
        .attr("transform", function (d) { return "translate(" + links + ",20)"; })
        ;

    balkenfig.selectAll("rect") // <-C
        .data(data.filter(function (d) { return d.kop == category; }))
        .exit().remove();

    // benchmarkstreepjes
    // uitleg bij benchmarktreepjes
    if (OKOteam < 1) {
        var toeltip = d3.tip()
            .attr('class', 'toeltip')
            .attr("x", 0)
            .attr("y", 0)
            .style("z-index", 2)
            .html(function (d, i) { return '<i>' + d.item + '</i></br></br>gemiddelde score in ' + gemeente + ': ' + d.score + '.<br>in alle andere regio\'s: ' + d.score_bm + '</span>'; });
    } else {
        var toeltip = d3.tip()
            .attr('class', 'toeltip')
            .attr("x", 0)
            .attr("y", 0)
            .style("z-index", 2)
            .html(function (d, i) { return '<i>' + d.item + '</i></br></br>gemiddelde score in alle deelnemende gemeenten: ' + d.score + '</span>'; });

    }

    if (OKOteam < 1) {
        balkenfig
            .selectAll("rect#bm")
            .data(data.filter(function (d) { return d.kop == category; }))
            .enter()
            .append("rect")
            .attr("height", balkhoogte + 6)
            .attr("width", 10)
            .attr("id", "bm")
            .attr("rx", 5)
            .attr("ry", 5)
            .style("fill", "#fabd15ff")
            .style("stroke", "red")
            .style("stroke-width", "0px")
            .style("opacity", 1)
            .attr("y", function (d, i) { return i * (balkhoogte + 5) + 33; })
            .attr("x", function (d) {
                return xScale(3) + "px";
            })
            .attr("transform", function (d) { return "translate(" + links + ",20)"; })
            .on('mouseover', toeltip.show)
            .on('mouseout', toeltip.hide);



        balkenfig
            .selectAll("rect#bm")
            .transition().duration(1000).attr("x", function (d) {
                return xScale(d.score_bm) + "px";
            });


        balkenfig
            .selectAll("rect#bm") // <-C
            .data(data.filter(function (d) { return d.kop == category; }))
            .exit().remove();
    }
    //normstreepjes
    balkenfig
        .selectAll("rect#bmz")
        .data(data.filter(function (d) { return d.kop == category; }))
        .enter()
        .append("rect")
        .attr("height", balkhoogte)
        .attr("width", 3)
        .attr("id", "bmz")
        .attr("y", function (d, i) { return i * (balkhoogte + 5) + 35; })
        .attr("x", function (d) {
            return xScale(4) + "px";
        })
        .style("fill", "red")
        .style("stroke", "red")
        .style("stroke-dasharray", ("2, 2"))
        .style("opacity", 0.2)
        .style("stroke-width", "0px")
        .attr("transform", function (d) { return "translate(" + links + ",20)"; });
    //cirkeltjes
    balkenfig
        .selectAll("circle")
        .data(data.filter(function (d) { return d.kop == category; }))
        .enter()
        .append("circle")
        .attr("cy", function (d, i) { return i * (balkhoogte + 5) + 35; })
        .style("fill", function (d, i) {
            if (i < 1) {
                return "#4450c6ff";
            } else {
                return "#82cae0";
            }

        })
        .style("stroke", "#FFFFFF")
        .style("stroke-width", "3px")
        .attr("r", balkhoogte / 2)
        .attr("cx", function (d) {
            return xScale(3) + "px";
        })
        .attr("transform", function (d) { return "translate(" + links + ",45)"; })
        .on('mouseover', toeltip.show)
        .on('mouseout', toeltip.hide);

    balkenfig.selectAll("circle")
        .transition().duration(1000).attr("cx", function (d) {
            return xScale(d.score) + "px";
        });

    balkenfig.selectAll("circle") // <-C
        .data(data.filter(function (d) { return d.kop == category; }))
        .exit().remove();

    //tekst in cirkeltje: 
    //jahaa     
    balkenfig
        .selectAll("text#labeltje")
        .data(data.filter(function (d) { return d.kop == category; }))
        .enter()
        .append("text")
        .attr("font-weight", "normal")
        .attr("id", "labeltje")
        .attr("height", 30)
        .attr("stroke-width", "0")
        .attr("font-size", "15")
        .attr("y", function (d, i) { return i * (balkhoogte + 5) + 38; })
        .attr("x", function (d) {
            return xScale(3) + "px";
        })
        .attr("text-anchor", "middle")
        .html(function (d, i) { return d.score })
        .attr("fill", "white")
        .attr("transform", function (d) { return "translate(" + links + ",46)"; })
        .on('mouseover', toeltip.show)
        .on('mouseout', toeltip.hide);

    balkenfig.call(toeltip);

    balkenfig
        .selectAll("text#labeltje")
        .transition().duration(1000).attr("x", function (d) {
            return xScale(d.score) + "px";
        });

    //tekst voor staven:
    balkenfig
        .selectAll("text#staven")
        .data(data.filter(function (d) { return d.kop == category; }))
        .enter()
        .append("text")
        .attr("id", "staven")
        .attr("height", balkhoogte)
        .attr("width", breedte)
        .attr("font-size", "15")
        .attr("font-color", function (d, i) {
            if (i < 1) {
                return "#4450c6";
            } else {
                return "#000000";
            }
        })
        //.attr("class", "item")
        .attr("y", function (d, i) { return i * (balkhoogte + 5) + 35; })
        .attr("x", -30)
        .attr("data-html", "true")
        .attr("text-anchor", "end")
        .html(function (d, i) {
            if (d.item.length > 90) {
                return breek(d.item);
            } else {
                return d.item
            }
        })
        .attr("transform", function (d) { return "translate(" + links + ",48)"; })
        ;

    balkenfig.selectAll(".item") // <-C
        .data(data.filter(function (d) { return d.kop == category; }))
        .exit().remove();

    //printknop
    /*
    balkenfig.append("image")
    .attr("xlink:href", "https://localhost/hansei/oko/wp-content/plugins/oko-sawemo/images/print.png" )
    .attr("x", 0)
    .attr("y", 0)
    .attr("width", grootte/3)
    .attr("height", grootte/3)
    .attr("cursor", "pointer")
    .on("mousedown", printPage())
    ;
*/


    //x-as
    var xAxis = d3.svg.axis()
        .scale(xScale)
        .orient("bottom")
        .ticks(5);
    // Add the X Axis
    balkenfig
        .append("g")
        .attr("class", "axis")
        .attr("transform", function (d) { return "translate(" + links + ",20)"; })
        .call(xAxis);

    //labeltje bij as

    balkenfig
        .append("text")
        .attr("id", "lab1")
        .attr("class", "catlab")
        .attr("height", 100)
        .attr("width", 50)
        .attr("fill", "red")
        .attr("x", 0)
        .attr("text-anchor", "middle")
        .html("oneens")
        .attr("transform", function (d) { return "translate(" + links + ",20)"; })
        ;
    balkenfig
        .append("text")
        .attr("id", "lab1")
        .attr("class", "catlab")
        .attr("height", 100)
        .attr("width", 50)
        .attr("fill", "red")
        .attr("x", breedte - 25)
        .attr("text-anchor", "middle")
        .html("eens")
        .attr("transform", function (d) { return "translate(" + links + ",20)"; })
        ;

}
function breek(hier) {
    var regel1 = hier.substring(0, 80) + " ...";
    //var regel2 = hier.substring(80);
    return regel1;// + "<br />" + regel2;
}

function printPage() {
    //return;
    var divElements = document.getElementById('main').innerHTML;
    var oldPage = document.body.innerHTML;
    document.body.innerHTML = "<link rel='stylesheet' href='../wp-content/plugins/oko-sawemo/css/print.css' type='text/css' /><body class='bodytext'>" + divElements + "</body>";
    window.print();
    document.body.innerHTML = oldPage;
}

function LaatZien(toon) {
    d3.select("#koptitels").selectAll("h3").remove();
    d3.select("#koptitelvragen").selectAll("h5").remove();
    d3.select("#rechts").selectAll("p").remove();
    d3.select("#links").selectAll("text").remove();
    d3.select("#links").selectAll("rect").remove();
    d3.select("#links").selectAll("circle").remove();
    //d3.select("#tips").html(ikonen[naam]);
    var x1 = document.getElementById("koptitels");
    var x4 = document.getElementById("koptitelvragen");
    var x2 = document.getElementById("rechts");
    var x3 = document.getElementById("links");
    for (z = 0; z < 10; z++) {
        if (z == toon) {
            x1.style.display = "block";
            d3.select("#koptitels").append("h3").html(ikonen[z].item).style("opacity", 0);
            d3.select("#koptitels").selectAll("h3").transition().duration(800).style("opacity", 1);
            x4.style.display = "block";
            d3.select("#koptitelvragen").append("h5").html(ikonen[z].vraag).style("opacity", 0);
            d3.select("#koptitelvragen").selectAll("h5").transition().duration(800).style("opacity", 1);
            x2.style.display = "block";
            d3.select("#rechts").append("p").html(ikonen[z].toelichting).style("opacity", 0);
            d3.select("#rechts").selectAll("p").transition().duration(800).style("opacity", 1);
            x3.style.display = "block";
            d3.select("#links").append("text").style("opacity", 0);
            d3.select("#links").selectAll("text").transition().duration(800).style("opacity", 1);
            d3.select("#links").append("rect").style("opacity", 0);
            d3.select("#links").selectAll("rect").transition().duration(800).style("opacity", 1);
            d3.select("#links").append("circle").style("opacity", 1);
            d3.select("#links").selectAll("circle")
                .data(databegin.filter(function (d) { return d.kop == toon; }))
                .transition().duration(5000).attr("cx", function (d) {
                    return (d.score * 2.5) + "px";
                });
            d3.select("#links").selectAll("circle").transition().duration(800).style("opacity", 1);
        }
    }
}
LaatZien(0);
select(0);

//$(document).foundation();