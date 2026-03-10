{{-- resources/views/sos/sos-chart-render.blade.php --}}
<!doctype html>
<html lang="en">

<head>

    {{-- Offline ApexCharts (must exist in public/vendor/apexcharts/) --}}
    <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>

</head>

<body>

    @php
        // Expect: $hourLabels = [0..23], $hourValues = [24 ints]
        // Provide safe defaults if not passed
        $hourLabels = $hourLabels ?? range(0, 23);
        $hourValues = $hourValues ?? array_fill(0, 24, 0);

        // Ensure correct length (24) even if caller passed wrong size
        $hourLabels = array_slice(array_pad($hourLabels, 24, 0), 0, 24);
        $hourValues = array_slice(array_pad($hourValues, 24, 0), 0, 24);

        // API endpoint (your case)
        $storeUrl = url('/api/sos/report/store-chart');
    @endphp

    <div class="card">
        <div class="title">Hourly SOS Calls (0–23)</div>


        <div id="barchart"></div>

        <div class="row">
            <div>
                <span id="barchartstatus" class="badge">Waiting…</span>
            </div>
            <div style="display:flex; gap:8px;display:none">
                <button class="btn" id="barchartbtnUpload">Export & Upload</button>
                <button class="btn" id="barchartbtnOpen" disabled>Open Saved Image</button>
            </div>
        </div>

        <pre id="barchartlog" style="display:none">Log:</pre>
    </div>

    <script>
        (async function() {
            const statusEl = document.getElementById('barchartstatus');
            const logEl = document.getElementById('barchartlog');
            const btnUpload = document.getElementById('barchartbtnUpload');
            const btnOpen = document.getElementById('barchartbtnOpen');

            function log(msg, obj) {
                const line = obj ? (msg + " " + JSON.stringify(obj, null, 2)) : msg;
                logEl.textContent += "\n" + line;
                console.log(msg, obj || "");
            }

            function setStatus(text, kind) {
                statusEl.textContent = text;
                statusEl.className = "badge" + (kind ? (" " + kind) : "");
            }

            // Safety checks
            if (typeof ApexCharts === 'undefined') {
                setStatus("ApexCharts not loaded", "err");
                log(
                    "ERROR: ApexCharts is undefined. Ensure file exists at /public/vendor/apexcharts/apexcharts.min.js"
                );
                return;
            }

            // Data from Blade
            const hourLabels = @json($hourLabels);
            const hourValues = @json($hourValues);

            let chart = null;
            let lastPublicUrl = null;

            const options = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: false
                    } // stable export
                },
                series: [{
                    name: 'SOS Calls',
                    data: hourValues
                }],
                xaxis: {
                    categories: hourLabels,
                    tickAmount: 24,
                    title: {
                        text: 'Hour (0–23)'
                    }
                },
                yaxis: {
                    min: 0,
                    title: {
                        text: 'Calls'
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '65%',
                        borderRadius: 4
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#137fec'],
                grid: {
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: (v) => `${v} calls`
                    }
                }
            };

            async function renderChart() {
                try {
                    setStatus("Rendering chart…");
                    chart = new ApexCharts(document.querySelector("#barchart"), options);
                    await chart.render();

                    // Wait a short tick for layout
                    await new Promise(r => setTimeout(r, 200));

                    if (typeof chart.dataURI !== 'function') {
                        setStatus("Export not supported (update apexcharts)", "err");
                        log(
                            "ERROR: chart.dataURI() is not available. Replace apexcharts.min.js with a newer version."
                        );
                        return;
                    }

                    setStatus("Chart ready", "ok");
                    log("Chart rendered successfully");
                } catch (e) {
                    setStatus("Render failed", "err");
                    log("Render error:", {
                        message: e.message,
                        stack: e.stack
                    });
                }
            }

            async function exportAndUpload() {
                try {
                    if (!chart) {
                        setStatus("Chart not ready", "err");
                        return;
                    }

                    setStatus("Exporting image…");
                    log("Exporting chart to PNG…");

                    // const {
                    //     blobURI
                    // } = await chart.dataURI();
                    // const blob = await fetch(blobURI).then(r => r.blob());


                    const uri = await chart.dataURI(); // may contain imgURI only

                    // console.log(uri);

                    const imageUrl = uri.blobURI || uri.imgURI;

                    // console.log(imageUrl);


                    if (!imageUrl) {
                        setStatus("Export failed", "err");
                        log("Export failed: no imgURI/blobURI returned", uri);
                        return;
                    }

                    // Convert to Blob (works for blobURI or data:image/png;base64,...)
                    const blob = await fetch(imageUrl).then(r => r.blob());





                    // console.log("blob", blob);





                    setStatus("Uploading…");
                    log("Uploading PNG (size bytes):", {
                        size: blob.size
                    });

                    const fd = new FormData();
                    fd.append('chart', blob, 'hourly_sos.png');
                    fd.append('filename', 'hourly_sos.png');



                    console.log("fd", fd);


                    const res = await fetch(@json($storeUrl), {
                        method: 'POST',
                        body: fd
                    });

                    let json = null;
                    try {
                        json = await res.json();
                    } catch (_) {}

                    if (!res.ok) {
                        setStatus("Upload failed", "err");
                        log("Upload failed:", {
                            status: res.status,
                            response: json
                        });
                        return;
                    }

                    setStatus("Uploaded OK", "ok");
                    log("Upload response:", json);

                    // Expected: { ok: true, path: "...", public_url: "..." }
                    lastPublicUrl = json && (json.public_url || null);

                    if (lastPublicUrl) {
                        btnOpen.disabled = false;
                        log("Saved image URL:", {
                            public_url: lastPublicUrl
                        });
                    } else {
                        log(
                            "Note: public_url not returned. You can open /storage/reports/charts/hourly_sos.png after storage:link"
                        );
                    }

                } catch (e) {
                    setStatus("Upload exception", "err");
                    log("Upload exception:", {
                        message: e.message,
                        stack: e.stack
                    });
                }
            }

            btnUpload.addEventListener('click', exportAndUpload);
            btnOpen.addEventListener('click', function() {
                if (lastPublicUrl) window.open(lastPublicUrl, "_blank");
            });

            renderChart();

            setTimeout(() => {
                exportAndUpload();

            }, 1000);

            // setTimeout(() => {
            //     window.location.href =
            //         "http://127.0.0.1:8000/api/sos_analytics_pdf?company_id=8";

            // }, 5000);
        })();
    </script>

</body>

</html>
