function UpdateStat(e) {
    if (e && e.preventDefault){
        e.preventDefault();
    }
    $.ajax({
        type: "POST",
        url: 'getStats',
        data: $(this).serialize(),
        success: function (response) {
            let jsonData = JSON.parse(response);

            if (jsonData.success === 1) {
                statsChart.data.datasets[0].data = jsonData.data;
                statsChart.update();

                $("#min").text(jsonData.min);
                $("#max").text(jsonData.max);
                $("#med").text(jsonData.med);
                $("#avg").text(jsonData.avg);
                $("#stdev").text(jsonData.stdev);
            } else {

            }
        }
    });
}