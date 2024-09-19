<?PHP
class Taart
{
    public function __CONSTRUCT()
    {

    }

    public function generate($series)
    {
        $this->series = $series;
        return $this->Create_Chart();
    }

    public function Create_Chart()
    {

        return "
                    <script>
                    Highcharts.setOptions({colors: ['#011763', '#e3032c', '#82cae0', '#3db28f', '#fabd15', '#4450c6']});
                    Highcharts.chart('domtaart', {
                        chart: {
                    
                            type: 'pie'
                        },
                        title: {
                            text: '',
                            align: 'left'
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.y} ({point.percentage:.1f} %)</b>'
                        },
                        plotOptions: {
                            pie: {
                                size: 150
                            }
                        },
                        series: [{
                            name: 'Aantal',
                            data: 
                            [" . $this->series . "]
                        }]
                    });
                    
	</script>";
    }
}

//$show=new HC_StackedGrouped_Bar();
//echo $show->generate("div","['a', 'b']", "{}");
?>