function UpdateStat(e) {
    if (e && e.preventDefault) {
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

/**
 * Makes request and copies its content to the clipboard.
 * @param request Url to send to.
 * @param callBack Function to be called on success.
 */
function CopyRequestToClipboard(request, callBack) {
    $.ajax({
        type: "GET",
        url: request,
        success: function (response) {
            copyToClipboard(response);

            if (callBack) {
                callBack();
            }
        }
    });
}

function copyToClipboard (str) {
    const el = document.createElement('textarea');
    el.value = str;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    el.select();
    el.setSelectionRange(0, 99999); /*For mobile devices*/
    document.execCommand('copy');
    document.body.removeChild(el);
}