<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Render Chart</title>

    <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div id="hourlySosChart" style="height:320px;"></div>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {

            const options = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: false
                    }
                },
                series: [{
                    name: 'SOS Calls',
                    data: @json($hourValues) // 24 values
                }],
                xaxis: {
                    categories: @json($hourLabels) // 0..23
                },
                yaxis: {
                    min: 0
                },
                colors: ['#137fec'],
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    bar: {
                        columnWidth: '65%',
                        borderRadius: 4
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#hourlySosChart"), options);
            await chart.render();

            // wait a bit for layout
            await new Promise(r => setTimeout(r, 300));

            // export to PNG as blob
            const {
                blobURI
            } = await chart.dataURI();
            const blob = await fetch(blobURI).then(r => r.blob());

            const fd = new FormData();
            fd.append('chart', blob, 'hourly_sos.png');

            const res = await fetch(@json(route('sos.report.storeChart')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: fd
            });

            const json = await res.json();

            if (!res.ok) {
                console.log(json);
                alert('Upload failed. Check console.');
                return;
            }

            // go to PDF route
            window.location.href = json.pdf_url;
        });
    </script>
</body>

</html>
