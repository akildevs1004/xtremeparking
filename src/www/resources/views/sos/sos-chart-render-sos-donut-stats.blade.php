<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
    <style>
        /* body {
            font-family: Arial, sans-serif;
            padding: 16px;
            color: #0f172a;
        } */

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
            max-width: 500px;
            margin: 0 auto;
        }

        .title {
            font-size: 16px;
            font-weight: 800;
            margin: 0 0 10px 0;
        }

        .muted {
            color: #64748b;
            font-size: 12px;
        }

        .row {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }

        .ok {
            background: #dcfce7;
            color: #166534;
        }

        .err {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            border: 1px solid #cbd5e1;
            background: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
        }

        #chart {
            height: 360px;
        }

        pre {
            margin-top: 10px;
            background: #0b1220;
            color: #dbeafe;
            padding: 10px;
            border-radius: 8px;
            overflow: auto;
            font-size: 12px;
        }
    </style>
</head>

<body>
    @php
        // Expect: $donutLabels, $donutSeries from controller, else defaults
        $donutLabels = $donutLabels ?? ['Resolved', 'Pending', 'Responded'];
        $donutSeries = $donutSeries ?? [0, 0, 0];

        $storeUrl = url('/api/sos/report/store-chart');
    @endphp

    <div class="card">
        <div class="title">DONUT SOS </div>

        <div id="chart"></div>

        <div class="row">
            <div><span id="status" class="badge">Waiting…</span></div>
            <div style="display:flex; gap:8px;display:none">
                <button class="btn" id="btnUpload">Export & Upload</button>
                <button class="btn" id="btnOpen" disabled>Open Saved Image</button>
            </div>
        </div>

        <pre id="donutlog" style="display:none">Log:</pre>
    </div>

    <script>
        (async function() {
            const statusEl = document.getElementById('status');
            const logEl = document.getElementById('donutlog');
            const btnUpload = document.getElementById('btnUpload');
            const btnOpen = document.getElementById('btnOpen');

            function log(msg, obj) {
                const line = obj ? (msg + " " + JSON.stringify(obj, null, 2)) : msg;
                logEl.textContent += "\n" + line;
                console.log(msg, obj || "");
            }

            function setStatus(text, kind) {
                statusEl.textContent = text;
                statusEl.className = "badge" + (kind ? (" " + kind) : "");
            }

            if (typeof ApexCharts === 'undefined') {
                setStatus("ApexCharts not loaded", "err");
                log("ERROR: ApexCharts is undefined. Check /public/vendor/apexcharts/apexcharts.min.js");
                return;
            }

            const labels = @json($donutLabels);
            const series = @json($donutSeries);

            let chart = null;
            let lastPublicUrl = null;

            const options = {
                chart: {
                    type: 'donut',
                    height: 360,
                    animations: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },

                labels: labels,
                series: series,
                colors: ['#22c55e', '#f59e0b', '#ef4444'], // GREEN, YELLOW, RED

                /* ---------- LEGEND (Right size & spacing) ---------- */
                legend: {
                    position: 'right',
                    horizontalAlign: 'left',
                    fontSize: '14px',
                    fontWeight: 600,
                    itemMargin: {
                        vertical: 8
                    },
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 10
                    },
                    formatter: function(seriesName, opts) {
                        const value = opts.w.globals.series[opts.seriesIndex];
                        const total = opts.w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                        const percent = total ? Math.round((value / total) * 100) : 0;
                        return `${seriesName} : <strong>${value} </strong>`;
                    }
                },

                /* ---------- DATA LABELS ON SLICES ---------- */
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '13px',
                        fontWeight: 600
                    },
                    dropShadow: {
                        enabled: false
                    },
                    formatter: function(val, opts) {
                        // show raw count, not percentage
                        return opts.w.globals.series[opts.seriesIndex];
                    }
                },

                /* ---------- DONUT CENTER LABELS ---------- */
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,

                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    offsetY: -6
                                },

                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 800,
                                    offsetY: 6,
                                    formatter: function(val) {
                                        return val; // count
                                    }
                                },

                                total: {
                                    show: true,
                                    label: 'Total SOS',
                                    fontSize: '13px',
                                    fontWeight: 700,
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },

                tooltip: {
                    y: {
                        formatter: (v) => `${v} calls`
                    }
                }
            };


            async function renderChart() {
                setStatus("Rendering chart…");
                chart = new ApexCharts(document.querySelector("#chart"), options);
                await chart.render();
                setStatus("Chart ready", "ok");
                log("Rendered donut chart", {
                    labels,
                    series
                });
            }

            async function exportAndUpload() {
                try {
                    if (!chart) return;

                    setStatus("Exporting…");
                    const uri = await chart.dataURI();
                    const imageUrl = uri.blobURI || uri.imgURI;

                    if (!imageUrl) {
                        setStatus("Export failed", "err");
                        log("Export failed: no imgURI/blobURI", uri);
                        return;
                    }

                    const blob = await fetch(imageUrl).then(r => r.blob());

                    setStatus("Uploading…");
                    const fd = new FormData();
                    fd.append('chart', blob, 'sos_status_donut.png');
                    fd.append('filename', 'sos_status_donut.png');

                    // Debug if needed:
                    // for (const [k,v] of fd.entries()) console.log("fd", k, v);

                    const res = await fetch(@json($storeUrl), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body: fd
                    });

                    const text = await res.text();
                    let json = null;
                    try {
                        json = JSON.parse(text);
                    } catch (_) {}

                    if (!res.ok) {
                        setStatus("Upload failed", "err");
                        log("Upload failed", {
                            status: res.status,
                            text,
                            json
                        });
                        return;
                    }

                    setStatus("Uploaded OK", "ok");
                    log("Upload response", json);

                    lastPublicUrl = json && (json.public_url || null);
                    if (lastPublicUrl) {
                        btnOpen.disabled = false;
                    }
                } catch (e) {
                    setStatus("Upload exception", "err");
                    log("Upload exception", {
                        message: e.message,
                        stack: e.stack
                    });
                }
            }

            btnUpload.addEventListener('click', exportAndUpload);
            btnOpen.addEventListener('click', () => {
                if (lastPublicUrl) window.open(lastPublicUrl, "_blank");
            });

            await renderChart();

            setTimeout(() => {
                exportAndUpload();

            }, 1000);
            // Auto-export (optional)
            // await exportAndUpload();
        })();
    </script>
</body>

</html>
